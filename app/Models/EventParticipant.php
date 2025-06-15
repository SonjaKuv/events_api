<?php
namespace App\Models;

class EventParticipants
{
    protected int $participantId;
    protected $suggestedStartDate;
    protected $suggestedStartTime;
    protected $suggestedEndDate;

    public function __construct($participantId, $suggestedStartDate, $suggestedStartTime, $suggestedEndDate) {
        $this->participantId = $participantId;
        $this->suggestedStartDate = $suggestedStartDate;
        $this->suggestedStartTime = $suggestedStartTime;
        $this->suggestedEndDate = $suggestedEndDate;
    }
}
