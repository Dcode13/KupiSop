<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Produk')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $category = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    // Field form
    public string $name = '';
    public ?int $category_id = null;
    public $price = '';
    public string $description = '';
    public bool $is_active = true;
    public string $imageData = '';
    public ?string $existingImage = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'imageData' => 'nullable|string',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'category_id', 'price', 'description', 'imageData', 'existingImage']);
        $this->is_active = true;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit(Product $product): void
    {
        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->category_id = $product->category_id;
        $this->price = $product->price;
        $this->description = $product->description ?? '';
        $this->is_active = $product->is_active;
        $this->existingImage = $product->image;
        $this->imageData = '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();
        $imageUpload = $this->decodeImageData();

        $data = [
            'name' => $this->name,
            'category_id' => $this->category_id,
            'price' => $this->price,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->imageData !== '') {
            $this->deleteStoredImage($this->existingImage);
            $data['image'] = $this->storeImage(
                $imageUpload['contents'],
                $imageUpload['extension'],
                $imageUpload['mime']
            );
        }

        Product::updateOrCreate(['id' => $this->editingId], $data);

        $this->showModal = false;
        $this->dispatch('notify', message: $this->editingId ? 'Produk diperbarui.' : 'Produk ditambahkan.');
        $this->reset(['editingId', 'name', 'category_id', 'price', 'description', 'imageData', 'existingImage']);
    }

    public function toggleActive(Product $product): void
    {
        $product->update(['is_active' => ! $product->is_active]);
        $this->dispatch('notify', message: 'Status produk diperbarui.');
    }

    public function delete(Product $product): void
    {
        $this->deleteStoredImage($product->image);
        $product->delete();
        $this->dispatch('notify', message: 'Produk dihapus.');
    }

    private function decodeImageData(): ?array
    {
        if ($this->imageData === '') {
            return null;
        }

        if (! preg_match('/^data:image\/(jpeg|png|webp|gif);base64,/', $this->imageData, $matches)) {
            throw ValidationException::withMessages([
                'imageData' => 'Foto produk harus berupa gambar JPG, PNG, WebP, atau GIF.',
            ]);
        }

        $encoded = substr($this->imageData, strpos($this->imageData, ',') + 1);
        $binary = base64_decode($encoded, true);

        if ($binary === false || @getimagesizefromstring($binary) === false) {
            throw ValidationException::withMessages([
                'imageData' => 'Foto produk tidak valid.',
            ]);
        }

        if (strlen($binary) > 2 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'imageData' => 'Ukuran foto produk maksimal 2 MB.',
            ]);
        }

        return [
            'contents' => $binary,
            'extension' => $matches[1] === 'jpeg' ? 'jpg' : $matches[1],
            'mime' => 'image/'.$matches[1],
        ];
    }

    private function storeImage(string $contents, string $extension, string $mime): string
    {
        $path = 'products/'.now()->format('Y/m').'/'.Str::uuid().'.'.$extension;

        $stored = Storage::disk($this->imageDisk())->put($path, $contents, [
            'ContentType' => $mime,
        ]);

        if (! $stored) {
            throw ValidationException::withMessages([
                'imageData' => 'Foto produk gagal disimpan ke Supabase Storage.',
            ]);
        }

        return $path;
    }

    private function deleteStoredImage(?string $image): void
    {
        if (! $image || str_starts_with($image, 'data:image/') || str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return;
        }

        Storage::disk($this->imageDisk())->delete($image);
    }

    private function imageDisk(): string
    {
        return Product::imageDisk();
    }

    public function render()
    {
        $products = Product::with('category')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->category, fn ($q) => $q->where('category_id', $this->category))
            ->latest()
            ->paginate(10);

        return view('livewire.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
