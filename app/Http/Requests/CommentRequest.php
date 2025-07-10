<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'slug' => 'required|exists:buildings,slug',
      'comment' => 'required|string|max:250',
    ];
  }
}
