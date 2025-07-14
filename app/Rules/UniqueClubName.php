<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Club;
use App\Models\ClubRegisterTracker;
class UniqueClubName implements ValidationRule
{

    public function passes($attribute, $value)
    {
        
        $existsInClub = Club::where('ten', $value)->exists();

        
        $existsInClubRegisterTracker = ClubRegisterTracker::where('name', $value)->exists();

       
        return !$existsInClub && !$existsInClubRegisterTracker;
    }


    public function message()
    {
        return 'The club name has already been registered.';
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //
    }
}
