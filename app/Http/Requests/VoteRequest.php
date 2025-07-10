<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class VoteRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'hash' => 'required|string|size:32',
      'slug' => 'required|exists:buildings,slug',
    ];
  }
}
