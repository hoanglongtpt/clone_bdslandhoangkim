<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    private function allowed(Request $request, Property $property): Property
    {
        abort_unless(Property::query()->visibleTo($request->user())->whereKey($property->id)->exists(), 403);

        return $property;
    }

    public function show(Request $request, Property $property)
    {
        $this->allowed($request, $property);
        $property->load(['project', 'customers', 'saleNotes', 'rentNotes', 'media']);

        return view('properties.show', compact('property'));
    }

    public function edit(Request $request, Property $property)
    {
        $this->allowed($request, $property);
        abort_unless($request->user()->canEditProperties(), 403);
        $projects = Project::orderBy('project_name')->get();

        return view('properties.edit', compact('property', 'projects'));
    }

    public function update(Request $request, Property $property)
    {
        $this->allowed($request, $property);
        abort_unless($request->user()->canEditProperties(), 403);
        $data = $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'], 'code' => ['required', 'string', 'max:100'],
            'tower' => ['nullable', 'string', 'max:100'], 'floor' => ['nullable', 'string', 'max:100'],
            'room' => ['nullable', 'string', 'max:100'], 'property_type' => ['nullable', 'string', 'max:255'],
            'interior' => ['nullable', 'string', 'max:255'], 'area' => ['nullable', 'numeric', 'min:0'],
            'price_sell' => ['nullable', 'numeric', 'min:0'], 'price_rent' => ['nullable', 'numeric', 'min:0'],
            'sales_commission' => ['nullable', 'numeric', 'min:0'], 'status_new' => ['nullable', 'string', 'max:255'],
            'rent_expiry' => ['nullable', 'date'], 'updated_date' => ['nullable', 'date'],
        ]);
        if (! $request->user()->isAdmin() && array_key_exists('project_id', $data)) {
            $allowedProjects = $request->user()->projects()->pluck('projects.id')
                ->push($property->project_id)->filter()->map(fn ($id) => (int) $id);
            abort_unless($data['project_id'] === null || $allowedProjects->contains((int) $data['project_id']), 403);
        }
        $before = $property->only(array_keys($data));
        $property->update($data);
        ActivityLog::record('property.updated', $property, "Cập nhật căn {$property->code}", ['before' => $before, 'after' => $property->only(array_keys($data))]);

        return redirect()->route('properties.show', $property)->with('success', 'Đã cập nhật căn hộ.');
    }
}
