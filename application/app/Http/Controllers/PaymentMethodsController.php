<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\PaymentMethod;
use Illuminate\Support\Str;

class PaymentMethodsController extends Controller
{

    public function showpaymentmethods()
    {
        $paymentMethods = PaymentMethod::all();
        return view('payment-methods.all-payment-methods', compact('paymentMethods'));
    }

    public function addpaymentmethod()
    {
        return view('payment-methods.add-payment-method');
    }

    public function storepaymentmethod(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:50',
            'code' => 'required|string|max:50',
            // 'image' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'active' => 'boolean',
            'credentials' => 'nullable|json',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $uniqueName = Str::uuid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('payment_methods', $uniqueName, 'public');
            $validatedData['image'] = $imagePath;
        }
        // Create new payment method
        if (PaymentMethod::create($validatedData)) {
            Session::flash('success', 'Payment Method added successfully.');
        } else {
            Session::flash('error', 'Failed to add payment method.');
        }

        return redirect()->route('allpaymentmethods');
    }

    public function editpaymentmethod($id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        return view('payment-methods.edit-payment-method', compact('paymentMethod'));
    }

    public function updatepaymentmethod(Request $request, $id)
    {
        // Fetch the payment method by ID
        $paymentMethod = PaymentMethod::findOrFail($id);
    
        $validatedData = $request->validate([
            'title' => 'required|string|max:50',
            'code' => 'required|string|max:50',
            // 'image' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'active' => 'boolean',
            'credentials' => 'nullable|json',
        ]);
    
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($paymentMethod->image) {
                Storage::disk('public')->delete($paymentMethod->image);
            }

            // Store the new image with a unique name
            $image = $request->file('image');
            $uniqueName = Str::uuid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('payment_methods', $uniqueName, 'public');
            $validatedData['image'] = $imagePath;
        }

        // Update payment method
        if ($paymentMethod->update($validatedData)) {
            Session::flash('success', 'Payment Method updated successfully.');
        } else {
            Session::flash('error', 'Failed to update payment method.');
        }
    
        return redirect()->route('allpaymentmethods');
    }

    public function updateStatus(Request $request, $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->active = $request->input('active');
        $paymentMethod->save();

        session()->flash('status', 'Payment method status updated successfully.');
        
        return response()->json(['success' => true]);
    }

    public function deletepaymentmethod($id)
    {
        if (PaymentMethod::findOrFail($id)->delete()) {
            Session::flash('success', 'Payment Method deleted successfully.');
        } else {
            Session::flash('error', 'Failed to delete payment method.');
        }

        return redirect()->route('allpaymentmethods');
    }

}
