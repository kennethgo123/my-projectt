<?php

namespace App\Livewire\Lawyer;

use App\Models\LawyerAvailability;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ManageAvailability extends Component
{
    public $availabilities = [];
    public $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    // For adding new time slots
    public $newDay = 'Monday';
    public $newStartTime = '09:00';
    public $newEndTime = '17:00';
    public $newHasLunchBreak = false;
    public $newLunchStartTime = '12:00';
    public $newLunchEndTime = '13:00';
    
    // For editing existing time slots
    public $editId = null;
    public $editDay = null;
    public $editStartTime = null;
    public $editEndTime = null;
    public $editHasLunchBreak = false;
    public $editLunchStartTime = null;
    public $editLunchEndTime = null;
    
    public $lawyerId = null;
    
    protected $rules = [
        'newDay' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        'newStartTime' => 'required|date_format:H:i',
        'newEndTime' => 'required|date_format:H:i|after:newStartTime',
        'newLunchStartTime' => 'nullable|required_if:newHasLunchBreak,true|date_format:H:i|after:newStartTime',
        'newLunchEndTime' => 'nullable|required_if:newHasLunchBreak,true|date_format:H:i|after:newLunchStartTime|before:newEndTime',
        'editDay' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        'editStartTime' => 'required|date_format:H:i',
        'editEndTime' => 'required|date_format:H:i|after:editStartTime',
        'editLunchStartTime' => 'nullable|required_if:editHasLunchBreak,true|date_format:H:i|after:editStartTime',
        'editLunchEndTime' => 'nullable|required_if:editHasLunchBreak,true|date_format:H:i|after:editLunchStartTime|before:editEndTime',
    ];
    
    private function getTargetUserId()
    {
        return $this->lawyerId ?? Auth::id();
    }
    
    public function mount()
    {
        $this->loadAvailabilities();
    }
    
    public function loadAvailabilities()
    {
        $userId = $this->getTargetUserId();
        $this->availabilities = LawyerAvailability::where('user_id', $userId)
            ->orderByRaw("FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->orderBy('start_time')
            ->get()
            ->toArray();
    }
    
    public function addTimeSlot()
    {
        $this->validate([
            'newStartTime' => 'required|date_format:H:i',
            'newEndTime' => 'required|date_format:H:i|after:newStartTime',
            'newLunchStartTime' => 'nullable|required_if:newHasLunchBreak,true|date_format:H:i|after:newStartTime',
            'newLunchEndTime' => 'nullable|required_if:newHasLunchBreak,true|date_format:H:i|after:newLunchStartTime|before:newEndTime',
        ]);
        
        // Check for overlaps
        $hasOverlap = $this->checkForOverlap($this->newDay, $this->newStartTime, $this->newEndTime);
        
        if ($hasOverlap) {
            session()->flash('error', 'This time slot overlaps with an existing time slot.');
            return;
        }
        
        // Create new availability
        LawyerAvailability::create([
            'user_id' => $this->getTargetUserId(),
            'day_of_week' => $this->newDay,
            'start_time' => $this->newStartTime,
            'end_time' => $this->newEndTime,
            'is_available' => true,
            'has_lunch_break' => $this->newHasLunchBreak,
            'lunch_start_time' => $this->newHasLunchBreak ? $this->newLunchStartTime : null,
            'lunch_end_time' => $this->newHasLunchBreak ? $this->newLunchEndTime : null,
        ]);
        
        // Reload availabilities
        $this->loadAvailabilities();
        
        // Reset form
        $this->resetAddForm();
        
        session()->flash('message', 'Time slot added successfully!');
    }
    
    public function prepareEdit($id)
    {
        $availability = LawyerAvailability::find($id);
        
        if ($availability && $availability->user_id === $this->getTargetUserId()) {
            $this->editId = $availability->id;
            $this->editDay = $availability->day_of_week;
            $this->editStartTime = substr($availability->start_time, 0, 5);
            $this->editEndTime = substr($availability->end_time, 0, 5);
            $this->editHasLunchBreak = $availability->has_lunch_break;
            $this->editLunchStartTime = $availability->lunch_start_time ? substr($availability->lunch_start_time, 0, 5) : '12:00';
            $this->editLunchEndTime = $availability->lunch_end_time ? substr($availability->lunch_end_time, 0, 5) : '13:00';
        }
    }
    
    public function updateTimeSlot()
    {
        if (!$this->editId) {
            return;
        }
        
        $this->validate([
            'editDay' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'editStartTime' => 'required|date_format:H:i',
            'editEndTime' => 'required|date_format:H:i|after:editStartTime',
            'editLunchStartTime' => 'nullable|required_if:editHasLunchBreak,true|date_format:H:i|after:editStartTime',
            'editLunchEndTime' => 'nullable|required_if:editHasLunchBreak,true|date_format:H:i|after:editLunchStartTime|before:editEndTime',
        ]);
        
        $availability = LawyerAvailability::find($this->editId);
        
        if (!$availability || $availability->user_id !== $this->getTargetUserId()) {
            session()->flash('error', 'Unauthorized action.');
            return;
        }
        
        // Check for overlaps (excluding this time slot)
        $hasOverlap = $this->checkForOverlap($this->editDay, $this->editStartTime, $this->editEndTime, $this->editId);
        
        if ($hasOverlap) {
            session()->flash('error', 'This time slot overlaps with an existing time slot.');
            return;
        }
        
        // Update availability
        $availability->update([
            'day_of_week' => $this->editDay,
            'start_time' => $this->editStartTime,
            'end_time' => $this->editEndTime,
            'has_lunch_break' => $this->editHasLunchBreak,
            'lunch_start_time' => $this->editHasLunchBreak ? $this->editLunchStartTime : null,
            'lunch_end_time' => $this->editHasLunchBreak ? $this->editLunchEndTime : null,
        ]);
        
        // Reload availabilities
        $this->loadAvailabilities();
        
        // Reset form
        $this->resetEditForm();
        
        session()->flash('message', 'Time slot updated successfully!');
    }
    
    public function toggleAvailability($id)
    {
        $availability = LawyerAvailability::find($id);
        
        if ($availability && $availability->user_id === $this->getTargetUserId()) {
            $availability->update([
                'is_available' => !$availability->is_available,
            ]);
            
            $this->loadAvailabilities();
            
            $status = $availability->is_available ? 'enabled' : 'disabled';
            session()->flash('message', "Time slot {$status} successfully!");
        }
    }
    
    public function deleteTimeSlot($id)
    {
        $availability = LawyerAvailability::find($id);
        
        if ($availability && $availability->user_id === $this->getTargetUserId()) {
            $availability->delete();
            
            $this->loadAvailabilities();
            
            session()->flash('message', 'Time slot deleted successfully!');
        }
    }
    
    private function checkForOverlap($day, $startTime, $endTime, $excludeId = null)
    {
        $query = LawyerAvailability::where('user_id', $this->getTargetUserId())
            ->where('day_of_week', $day)
            ->where('is_available', true);
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->where(function ($q) use ($startTime, $endTime) {
            // Case 1: Start time is inside another time slot
            $q->orWhere(function ($q1) use ($startTime) {
                $q1->where('start_time', '<=', $startTime)
                    ->where('end_time', '>', $startTime);
            });
            
            // Case 2: End time is inside another time slot
            $q->orWhere(function ($q1) use ($endTime) {
                $q1->where('start_time', '<', $endTime)
                    ->where('end_time', '>=', $endTime);
            });
            
            // Case 3: New time slot completely contains an existing time slot
            $q->orWhere(function ($q1) use ($startTime, $endTime) {
                $q1->where('start_time', '>=', $startTime)
                    ->where('end_time', '<=', $endTime);
            });
        })->exists();
    }
    
    private function resetAddForm()
    {
        $this->newDay = 'Monday';
        $this->newStartTime = '09:00';
        $this->newEndTime = '17:00';
        $this->newHasLunchBreak = false;
        $this->newLunchStartTime = '12:00';
        $this->newLunchEndTime = '13:00';
    }
    
    private function resetEditForm()
    {
        $this->editId = null;
        $this->editDay = null;
        $this->editStartTime = null;
        $this->editEndTime = null;
        $this->editHasLunchBreak = false;
        $this->editLunchStartTime = null;
        $this->editLunchEndTime = null;
    }
    
    public function render()
    {
        return view('livewire.lawyer.manage-availability');
    }
}
