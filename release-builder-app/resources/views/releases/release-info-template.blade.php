:alphabet-white-r::alphabet-white-e::alphabet-white-l::alphabet-white-e::alphabet-white-a::alphabet-white-s::alphabet-white-e: :alphabet-white-exclamation:
RELEASE *'{{ $release->name }}'*

Hi, team!
New release draft started and delivered on `release` environment!

*Services affected:*
@foreach($release->services as $service)
- {{ $service->repository_name }} (PR: [[PLACE LINK TO PR HERE]])
@endforeach

*Release contains these tasks:*
@foreach($tasks as $task)
[#{{ $task->getId() }}]({{ $task->getUrl() }}) - {!! $task->getTitle() !!}
@endforeach

We plan to release it on prod on [[ SET YOUR DATE HERE ]].

Before this date we have a time to test release and make a regression.
@Edo @Sanela @Selmir PM @Bernard Wiesner @daniel-y

:exclamation: Feel free to write me about tasks that need to add in this release!
