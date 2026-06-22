<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\LoyaltyReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FidelizacionController extends Controller
{
    public function index()
    {
        return view('fidelizacion');
    }

    public function buscar(Request $request)
    {
        $v = Validator::make($request->all(), [
            'phone' => 'required|string|min:6|max:20',
        ]);
        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $customer = Customer::where('phone', $request->phone)->first();

        if (! $customer) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'    => true,
            'customer' => $this->customerData($customer),
            'rewards'  => $this->rewardsList((int) $customer->loyalty_points),
        ]);
    }

    public function registrar(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name'       => 'required|string|max:100',
            'phone'      => 'required|string|min:6|max:20',
            'ci'         => 'required|string|max:20',
            'birth_date' => 'required|date|before:today',
        ], [
            'name.required'       => 'El nombre es obligatorio.',
            'phone.required'      => 'El teléfono es obligatorio.',
            'ci.required'         => 'El carnet de identidad es obligatorio.',
            'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
            'birth_date.before'   => 'La fecha de nacimiento debe ser anterior a hoy.',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        // Si el teléfono ya existe, mostrar la cuenta existente
        $existing = Customer::where('phone', $v->validated()['phone'])->first();
        if ($existing) {
            return response()->json([
                'success'         => true,
                'already_existed' => true,
                'customer'        => $this->customerData($existing),
                'rewards'         => $this->rewardsList((int) $existing->loyalty_points),
            ]);
        }

        $branch   = Branch::where('is_active', true)->first();
        $customer = Customer::create([
            'name'       => $v->validated()['name'],
            'phone'      => $v->validated()['phone'],
            'ci'         => $v->validated()['ci'],
            'birth_date' => $v->validated()['birth_date'],
            'branch_id'  => $branch?->id,
        ]);

        return response()->json([
            'success'         => true,
            'already_existed' => false,
            'customer'        => $this->customerData($customer),
            'rewards'         => $this->rewardsList(0),
        ]);
    }

    private function customerData(Customer $customer): array
    {
        return [
            'name'           => $customer->name,
            'loyalty_points' => (int) $customer->loyalty_points,
            'total_visits'   => (int) $customer->total_visits,
            'member_since'   => $customer->created_at->format('M Y'),
            'is_birthday'    => $customer->isBirthday(),
        ];
    }

    private function rewardsList(int $points): array
    {
        return LoyaltyReward::active()->get()->map(fn ($r) => [
            'name'            => $r->name,
            'description'     => $r->description,
            'points_required' => $r->points_required,
            'can_redeem'      => $points >= $r->points_required,
        ])->values()->toArray();
    }
}
