<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyCustomerController extends Controller
{
    private function authorizeProperty(Request $request, Property $property): void
    {
        abort_unless(Property::query()->visibleTo($request->user())->whereKey($property->id)->exists(), 403);
    }

    public function store(Request $request, Property $property)
    {
        $this->authorizeProperty($request, $property);
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone1' => ['required', 'string', 'max:30', 'regex:/^[0-9+().\s-]{7,30}$/'],
        ], [
            'full_name.required' => 'Vui lòng nhập tên khách hàng.',
            'phone1.required' => 'Vui lòng nhập số điện thoại.',
            'phone1.regex' => 'Số điện thoại không đúng định dạng.',
        ]);

        $phone = preg_replace('/\s+/', '', trim($data['phone1']));
        $customer = DB::transaction(function () use ($property, $data, $phone) {
            $customer = Customer::query()->where('phone1', $phone)->first();
            if (! $customer) {
                $customer = Customer::create(['full_name' => trim($data['full_name']), 'phone1' => $phone]);
            }
            $property->customers()->syncWithoutDetaching([$customer->id]);

            return $customer;
        });

        ActivityLog::record('customer.attached', $property, "Thêm khách {$customer->full_name} vào căn {$property->code}", [
            'customer_id' => $customer->id, 'phone1' => $customer->phone1,
        ]);

        return back()->with('success', 'Đã thêm khách hàng vào căn hộ.');
    }

    public function destroy(Request $request, Property $property, Customer $customer)
    {
        $this->authorizeProperty($request, $property);
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($property->customers()->whereKey($customer->id)->exists(), 404);

        $property->customers()->detach($customer->id);
        ActivityLog::record('customer.detached', $property, "Xóa khách {$customer->full_name} khỏi căn {$property->code}", [
            'customer_id' => $customer->id, 'phone1' => $customer->phone1,
        ]);

        return back()->with('success', 'Đã xóa khách hàng khỏi căn hộ.');
    }
}
