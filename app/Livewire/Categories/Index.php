<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Kategori')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit(Category $category): void
    {
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Category::updateOrCreate(
            ['id' => $this->editingId],
            ['name' => $this->name, 'slug' => Str::slug($this->name)]
        );

        $this->showModal = false;
        $this->dispatch('notify', message: $this->editingId ? 'Kategori diperbarui.' : 'Kategori ditambahkan.');
        $this->reset(['editingId', 'name']);
    }

    public function delete(Category $category): void
    {
        if ($category->products()->exists()) {
            $this->dispatch('notify', message: 'Tidak bisa menghapus: kategori masih punya produk.', type: 'error');

            return;
        }

        $category->delete();
        $this->dispatch('notify', message: 'Kategori dihapus.');
    }

    public function render()
    {
        $categories = Category::withCount('products')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);

        return view('livewire.categories.index', compact('categories'));
    }
}
