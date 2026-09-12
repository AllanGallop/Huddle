<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Customers')]
class Index extends Component
{
    public string $search = '';

    public bool $showCustomerModal = false;

    public ?int $editingCustomerId = null;

    public string $customer_name = '';

    public string $customer_address = '';

    public string $customer_email = '';

    public string $customer_telephone = '';

    public string $customer_type = 'domestic';

    #[Computed]
    public function customers()
    {
        $search = trim($this->search);

        return Customer::query()
            ->withCount('projects')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';

                $query->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('telephone', 'like', $like)
                        ->orWhere('address', 'like', $like);
                });
            })
            ->orderBy('name')
            ->get();
    }

    public function openCreateCustomerModal(): void
    {
        $this->resetCustomerForm();
        $this->showCustomerModal = true;
    }

    public function openEditCustomerModal(int $customerId): void
    {
        $customer = Customer::query()->findOrFail($customerId);

        $this->editingCustomerId = $customer->id;
        $this->customer_name = $customer->name;
        $this->customer_address = $customer->address ?? '';
        $this->customer_email = $customer->email ?? '';
        $this->customer_telephone = $customer->telephone ?? '';
        $this->customer_type = $customer->type;
        $this->showCustomerModal = true;
    }

    public function closeCustomerModal(): void
    {
        $this->showCustomerModal = false;
        $this->resetCustomerForm();
        $this->resetValidation();
    }

    public function saveCustomer(): void
    {
        $validated = $this->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_address' => ['nullable', 'string', 'max:2000'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_telephone' => ['nullable', 'string', 'max:50'],
            'customer_type' => ['required', Rule::in(Customer::TYPES)],
        ]);

        $data = [
            'name' => $validated['customer_name'],
            'address' => $validated['customer_address'] ?: null,
            'email' => $validated['customer_email'] ?: null,
            'telephone' => $validated['customer_telephone'] ?: null,
            'type' => $validated['customer_type'],
        ];

        if ($this->editingCustomerId) {
            Customer::query()->findOrFail($this->editingCustomerId)->update($data);
            session()->flash('status', __('Customer updated successfully.'));
        } else {
            Customer::create($data);
            session()->flash('status', __('Customer created successfully.'));
        }

        $this->closeCustomerModal();
        unset($this->customers);
    }

    public function deleteCustomer(int $customerId): void
    {
        Customer::query()->findOrFail($customerId)->delete();

        unset($this->customers);
        session()->flash('status', __('Customer deleted successfully.'));
    }

    protected function resetCustomerForm(): void
    {
        $this->reset([
            'editingCustomerId',
            'customer_name',
            'customer_address',
            'customer_email',
            'customer_telephone',
        ]);
        $this->customer_type = 'domestic';
    }

    public function render()
    {
        return view('livewire.customers.index');
    }
}
