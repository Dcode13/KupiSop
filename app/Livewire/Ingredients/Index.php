<?php

namespace App\Livewire\Ingredients;

use App\Models\Ingredient;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Bahan Baku')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public bool $onlyLow = false;

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $unit = 'pcs';
    public $stock = 0;
    public $min_stock = 0;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:30',
            'stock' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'stock', 'min_stock']);
        $this->unit = 'pcs';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit(Ingredient $ingredient): void
    {
        $this->editingId = $ingredient->id;
        $this->name = $ingredient->name;
        $this->unit = $ingredient->unit;
        $this->stock = $ingredient->stock;
        $this->min_stock = $ingredient->min_stock;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Ingredient::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'unit' => $this->unit,
                'stock' => $this->stock,
                'min_stock' => $this->min_stock,
            ]
        );

        $this->showModal = false;
        $this->dispatch('notify', message: $this->editingId ? 'Bahan baku diperbarui.' : 'Bahan baku ditambahkan.');
        $this->reset(['editingId', 'name', 'stock', 'min_stock']);
    }

    public function delete(Ingredient $ingredient): void
    {
        $ingredient->delete();
        $this->dispatch('notify', message: 'Bahan baku dihapus.');
    }

    public function render()
    {
        $ingredients = Ingredient::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->onlyLow, fn ($q) => $q->whereColumn('stock', '<=', 'min_stock'))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.ingredients.index', compact('ingredients'));
    }
}
