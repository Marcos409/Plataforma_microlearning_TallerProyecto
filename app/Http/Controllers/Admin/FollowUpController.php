<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    /**
     * Guardar un nuevo seguimiento
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date|after:now',
            'type' => 'required|in:call,meeting,video,email',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Por ahora solo guardamos en sesión
        // Más adelante puedes crear la tabla follow_ups
        
        session()->flash('success', '✓ Seguimiento agendado correctamente para el ' . 
            \Carbon\Carbon::parse($request->scheduled_at)->format('d/m/Y H:i'));
        
        return redirect()->back();
    }
}