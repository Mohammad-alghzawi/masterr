<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Cart;
use App\Models\Checkout;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = Auth::user();
        $products = [];
        $checkouts = Checkout::where("user_id", $user->id)->where('id', '>', 1)->get();
        
        foreach ($checkouts as $checkout) {
            $carts = Cart::where('checkout_id', $checkout->id)->get();
            foreach ($carts as $cart) {
                array_push($products, [$checkout->id => $cart->product->product_name]);
            }
        }
        return view('profile.edit', [
            'user' => $user,
            'products' => $products,
            'checkouts' => $checkouts,
        ]);
        // return view('profile.edit', [
        //     'user' => $user,
        // ]->with('products', $products))->with('checkouts',$checkouts);
    }

    /**
     * Update the user's profile information.
     */


        public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Update the user's profile data (name and email)
        $user->fill($request->only(['name', 'email']));

        // Check if the user uploaded a new image
       
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $extenstion = $file->getClientOriginalExtension();
            $filename = time().'.'.$extenstion;
            $file->move('images', $filename);
            $user->avatar = $filename;
        }
            


            // $imagePath = $request->file('image')->store('images/users');
            
        

        // Update the user's phone number
        $user->phone = $request->input('phone');

        // Reset email verification if email is changed
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Save the user's updated profile
        $user->save();
        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
