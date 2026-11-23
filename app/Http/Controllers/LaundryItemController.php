<?php

namespace App\Http\Controllers;

use App\Models\LaundryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LaundryItemController extends Controller
{
    public function index(): View
    {
        $laundry_items = LaundryItem::latest()->paginate(10);

        return view('admin.laundry_items.index', compact('laundry_items'));
    }

    public function create(): View
    {
        return view('admin.laundry_items.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['image'] = $request->hasFile('image')
            ? $request->file('image')->store('laundry_items', 'public')
            : null;

        LaundryItem::create($validated);

        return redirect()->route('laundry_items.index')->with('success', 'Data layanan berhasil ditambahkan!');
    }

    public function edit(LaundryItem $laundryItem): View
    {
        return view('admin.laundry_items.edit', compact('laundryItem'));
    }

    public function update(Request $request, LaundryItem $laundryItem): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $previousImage = $laundryItem->image;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('laundry_items', 'public');
        } else {
            unset($validated['image']);
        }

        $laundryItem->update($validated);

        if (isset($validated['image']) && $previousImage) {
            Storage::disk('public')->delete($previousImage);
        }

        return redirect()->route('laundry_items.index')->with('success', 'Data layanan berhasil diperbarui!');
    }

    public function destroy(LaundryItem $laundryItem): RedirectResponse
    {
        $image = $laundryItem->image;
        $laundryItem->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return redirect()->route('laundry_items.index')->with('success', 'Data layanan berhasil dihapus!');
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:Cuci,Setrika,Paket'],
            'price' => ['required', 'integer', 'min:0'],
            'description' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];
    }
}
