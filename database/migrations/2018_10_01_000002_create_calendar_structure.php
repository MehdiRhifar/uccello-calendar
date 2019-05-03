<?php

use Illuminate\Database\Migrations\Migration;
use Uccello\Core\Database\Migrations\Traits\TablePrefixTrait;
use Uccello\Core\Models\Module;
use Uccello\Core\Models\Domain;

class CreateCalendarStructure extends Migration
{
    use TablePrefixTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $module = $this->createModule();
        $this->activateModuleOnDomains($module);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Module::where('name', 'calendar')->forceDelete();
    }

    protected function createModule()
    {
        $module = new  Module();
        $module->name = 'calendar';
        $module->icon = 'date_range';
        $module->model_class = null;
        $module->data = ["package" => "uccello/calendar", "menu" => 'uccello.index'];
        $module->save();

        return $module;
    }

    protected function activateModuleOnDomains($module)
    {
        $domains = Domain::all();
        foreach ($domains as $domain) {
            $domain->modules()->attach($module);
        }
    }
}
