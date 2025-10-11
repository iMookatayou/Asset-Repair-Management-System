<div class="mb-3">
    <label for="department_id" class="form-label">แผนก</label>
    <select name="department_id" id="department_id" class="form-select">
        <option value="">-- เลือกแผนก --</option>
        @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
            <option value="{{ $dept->id }}" {{ old('department_id', $asset->department_id ?? '') == $dept->id ? 'selected' : '' }}>
                {{ $dept->name }}
            </option>
        @endforeach
    </select>
</div>
