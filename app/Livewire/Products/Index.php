<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Produk')]
class Index extends Component
{
    use WithFileUploads, WithPagination;

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
    public $image;          // file upload baru
    public ?string $existingImage = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048', // maks 2MB
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
        $this->reset(['editingId', 'name', 'category_id', 'price', 'description', 'image', 'existingImage']);
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
        $this->image = null;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'category_id' => $this->category_id,
            'price' => $this->price,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        // Upload gambar baru -> simpan ke storage/app/public/products
        if ($this->image) {
            if ($this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }
            $data['image'] = $this->image->store('products', 'public');
        }

        Product::updateOrCreate(['id' => $this->editingId], $data);

        $this->showModal = false;
        $this->dispatch('notify', message: $this->editingId ? 'Produk diperbarui.' : 'Produk ditambahkan.');
        $this->reset(['editingId', 'name', 'category_id', 'price', 'description', 'image', 'existingImage']);
    }

    public function toggleActive(Product $product): void
    {
        $product->update(['is_active' => ! $product->is_active]);
        $this->dispatch('notify', message: 'Status produk diperbarui.');
    }

    public function delete(Product $product): void
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        $this->dispatch('notify', message: 'Produk dihapus.');
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
