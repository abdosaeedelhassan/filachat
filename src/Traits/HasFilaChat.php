<?php

namespace JaOcero\FilaChat\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use JaOcero\FilaChat\Models\FilaChatAgent;
use JaOcero\FilaChat\Models\FilaChatConversation;
use JaOcero\FilaChat\Models\FilaChatGroup;

trait HasFilaChat
{
    public static function getAllAgentIds(): array
    {
        return FilaChatAgent::query()->where('agentable_type', config('filachat.agent_model'))->pluck('agentable_id')->toArray();
    }

    public function sentConversations(): MorphMany
    {
        return $this->morphMany(FilaChatConversation::class, 'senderable');
    }

    public function receivedConversations(): MorphMany
    {
        return $this->morphMany(FilaChatConversation::class, 'receiverable');
    }

    public function allConversations()
    {
        return FilaChatConversation::where(function ($query) {
            $query->where('senderable_type', $this->getMorphClass())
                ->where('senderable_id', $this->id);
        })->orWhere(function ($query) {
            $query->where('receiverable_type', $this->getMorphClass())
                ->where('receiverable_id', $this->id);
        })->orWhere(function ($query) {
            $query->where('receiverable_type', FilaChatGroup::class)
                ->whereHas('group', function ($query) {
                    $query->whereHas('members', function ($query) {
                        $query->where('member_id', $this->id);
                    });
                });
        });
    }

    public function isAgent(): bool
    {
        return $this->agents()->exists();
    }

    public function agents(): MorphMany
    {
        return $this->morphMany(FilaChatAgent::class, 'agentable');
    }
}
