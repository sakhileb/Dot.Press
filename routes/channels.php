<?php

use App\Broadcasting\SlideChannelAuthorizer;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| A slide's private channel carries live canvas edits between everyone
| who has it open -- authorization mirrors SlidePolicy::view (team
| membership on the slide's deck/project). See SlideChannelAuthorizer for
| the actual check and why it's factored out into its own class.
|
*/

Broadcast::channel('slide.{slideId}', fn ($user, int $slideId) => SlideChannelAuthorizer::authorize($user, $slideId));
