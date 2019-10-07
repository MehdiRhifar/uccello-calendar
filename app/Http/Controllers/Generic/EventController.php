<?php

namespace Uccello\Calendar\Http\Controllers\Generic;

use Uccello\Calendar\CalendarEntityEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use stdClass;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Calendar\CalendarAccount;


class EventController extends Controller
{
    /**
     * Check user permissions
     */
    protected function checkPermissions()
    {
        $this->middleware('uccello.permissions:retrieve');
    }

    /**
     * Returns all events for a given service
     *
     * @param Domain $domain
     * @param [type] $type
     * @param Module $module
     * @return array
     */
    protected function list(Domain $domain, $type, Module $module, $params=[])
    {
        if(request()->has('start'))
            $params['start'] = request('start');

        if(request()->has('end'))
            $params['end'] = request('end');

        if(!array_key_exists('user_id', $params))
            $params['user_id'] = auth()->id();
        
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\EventController';
        $calendarType = new $calendarClass();
        return $calendarType->list($domain, $module, $params);
    }

    public function all(Domain $domain, Module $module, $params=[])
    {
        $types = \Uccello\Calendar\CalendarTypes::all();
        $globalEvents = [];

        foreach($types as $calendarType)
        {
            $events = $this->list($domain, $calendarType->name, $module, $params);
            $globalEvents = array_merge($globalEvents, $events);
        }

        return $globalEvents;
    }

    protected function create(Domain $domain, Module $module)
    {
        $type = request('type');
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass = $calendarTypeModel->namespace.'\EventController';

        $calendarType = new $calendarClass();
        return $calendarType->create($domain, $module);
    }

    public function retrieve(Domain $domain, Module $module, $returnJson=true, $params=[])
    {
        if(request()->has('type'))
            $type = request('type');
        else
            $type = $params['type'];

        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass = $calendarTypeModel->namespace.'\EventController';

        $calendarType = new $calendarClass();
        return $calendarType->retrieve($domain, $module, $returnJson, $params);
    }

    protected function update(Domain $domain, Module $module)
    {
        $type = request('type');
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass = $calendarTypeModel->namespace.'\EventController';

        $calendarType = new $calendarClass();
        return $calendarType->update($domain, $module);
    }

    protected function delete(Domain $domain, Module $module)
    {
        $type = request('type');
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass = $calendarTypeModel->namespace.'\EventController';

        $calendarType = new $calendarClass();
        return $calendarType->delete($domain, $module);
    }

    public static function generateEntityLink(Domain $domain)
    {
        $module = Module::where('name', 'calendar')->first();
        $uccelloLink = '';
        if(config('calendar.event.comment'))
            $uccelloLink = '<br/>#'.uctrans('before_url', $module).env('APP_NAME').' :';

        if (uccello()->useMultiDomains()) {
            $uccelloLink.= ' '.env('APP_URL').'/'.$domain->id.'/'.request('moduleName').'/'.request('recordId').'/link';
        } else {
            $uccelloLink.= env('APP_URL').'/'.request('moduleName').'/'.request('recordId').'/link';
        }

        if(config('calendar.event.comment'))
            $uccelloLink.=' - '.uctrans('after_url', $module).'.#';

        return $uccelloLink;
    }

    public function classify(Domain $d, Module $module, Request $request)
    {
        if($request->input('start') && $request->input('end'))
            Artisan::call('events:classify', [
                'user_id' => auth()->id(),
                'start' => $request->input('start'),
                'end' => $request->input('end')
            ]);
        else
            return "'stard' date and 'end' date are requiered";
    }

    public function related(Domain $d, Module $module, Request $request)
    {
        if($request->input('module_id') && $request->input('entity_id'))
        {
            $entityEvent = CalendarEntityEvent::where([
                'module_id' => $request->input('module_id'),
                'entity_id' => $request->input('entity_id')
            ])->first();
            return $entityEvent->events;
        }
    }
}
