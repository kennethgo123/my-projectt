<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }
    
    /**
     * Check if user has the required permission
     *
     * @param string $permission
     * @return bool
     */
    protected function hasPermission($permission)
    {
        return auth()->user()->hasPermission($permission);
    }
    
    /**
     * Check if user can view users
     *
     * @return bool
     */
    protected function canViewUsers()
    {
        return $this->hasPermission('view_user_list');
    }
    
    /**
     * Check if user can approve users
     *
     * @return bool
     */
    protected function canApproveUsers()
    {
        return $this->hasPermission('approve_users');
    }
    
    /**
     * Check if user can deactivate users
     *
     * @return bool
     */
    protected function canDeactivateUsers()
    {
        return $this->hasPermission('deactivate_users');
    }
    
    /**
     * Check if user can view sales panel
     *
     * @return bool
     */
    protected function canViewSalesPanel()
    {
        return $this->hasPermission('view_sales_panel');
    }
    
    /**
     * Check if user can manage subscriptions
     *
     * @return bool
     */
    protected function canManageSubscriptions()
    {
        return $this->hasPermission('manage_subscriptions');
    }
    
    /**
     * Check if user can manage law services
     *
     * @return bool
     */
    protected function canManageLawServices()
    {
        return $this->hasPermission('manage_law_services');
    }
    
    /**
     * Check if user can delete law services
     *
     * @return bool
     */
    protected function canDeleteLawServices()
    {
        return $this->hasPermission('delete_law_services');
    }
}
