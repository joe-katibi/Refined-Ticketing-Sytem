<?php

namespace Modules\Escalations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Escalations\Entities\Source;
use Illuminate\Support\Facades\Auth;

class SourceController extends Controller
{
    public function index(Request $request)
    {
        $query = Source::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhere('created_by', 'like', "%{$search}%")
                  ->orWhere('edited_by', 'like', "%{$search}%");

            });
        }

        $sources = $query->paginate(10)->withQueryString();
        return view('escalations::source.index', compact('sources'));
    }

    public function create()
    {
        return view('escalations::source.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $validated['created_by'] = Auth::user()->id;
        Source::create($validated);
        return redirect()->route('sources.index')->with('success', 'Source created successfully.');
    }

    public function show(Source $source)
    {
        return view('escalations::source.show', compact('source'));
    }

    public function edit(Source $source)
    {
        return view('escalations::source.edit', compact('source'));
    }

    public function inactive(Source $source)
    {
        $source->status = 'Inactive';
        $source->edited_by = Auth::user()->id;
        $source->save();
        return redirect()->route('sources.index')->with('success', 'Source inactivated successfully.');
    }

    public function active(Source $source)
    {
        $source->status = 'Active';
        $source->edited_by = Auth::user()->id;
        $source->save();
        return redirect()->route('sources.index')->with('success', 'Source activated successfully.');
    }

    public function deactive(Source $source)
    {
        return $this->active($source);
    }

    public function update(Request $request, Source $source)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $validated['edited_by'] = Auth::user()->id;
        $source->update($validated);
        return redirect()->route('sources.index')->with('success', 'Source updated successfully.');
    }

    public function destroy(Source $source)
    {
        $source->delete();
        return redirect()->route('sources.index')->with('success', 'Source deleted successfully.');
    }
}
