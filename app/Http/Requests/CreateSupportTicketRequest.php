<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSupportTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // All authenticated users can create tickets
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject' => 'required|string|min:5|max:255',
            'message' => 'required|string|min:10|max:5000',
            'category' => 'required|string|in:technical,billing,account,general,other',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|string|max:255', // Store file paths or URLs
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'subject.required' => 'Please provide a subject for your ticket.',
            'subject.min' => 'Subject must be at least 5 characters.',
            'message.required' => 'Please describe your issue.',
            'message.min' => 'Description must be at least 10 characters.',
            'category.in' => 'Please select a valid category.',
            'priority.in' => 'Please select a valid priority level.',
        ];
    }
}