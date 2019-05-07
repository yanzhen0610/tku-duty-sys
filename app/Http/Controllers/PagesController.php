<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function usersAndGroups()
    {
        $table_a_data = UsersController::getUsersData();
        $table_b_data = GroupsController::getGroupsData();
        return view('pages.dual_edit_table', compact(['table_a_data', 'table_b_data']));
    }

    public function areasAndShifts()
    {
        $table_a_data = AreasController::getAreasData();
        $table_b_data = ShiftsController::getShiftsData();
        return view('pages.dual_edit_table', compact(['table_a_data', 'table_b_data']));
    }
}
