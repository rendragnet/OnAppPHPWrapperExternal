<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Managing Users
 *
 * Users are created by administrators and have access only to those actions
 * which are specified by the administrator. You can add as many users as you
 * need. When creating, you can edit User Details, track Payments, and set the
 * Limits.
 * With OnApp you can assign resource limits to users. This will prevent users
 * from exceeding the resources you specify.
 *
 * @category    API wrapper
 * @package        OnApp
 * @author        Andrew Yatskovets
 * @copyright    (c) 2011 OnApp
 * @link        http://www.onapp.com/
 * @see            OnApp
 */

/**
 *
 */
define( 'ONAPP_GETRESOURCE_SUSPEND_USER', 'suspend' );

/**
 *
 */
define( 'ONAPP_GETRESOURCE_ACTIVATE', 'activate' );

/**
 *
 */
define( 'ONAPP_GETRESOURCE_NETWORKS_LIST_BY_GROUP_ID', 'get_list_by_group_id' );

/**
 *
 */
define( 'ONAPP_GETRESOURCE_DELETE_USER', 'delete_user' );

/**
 * Users
 *
 * The User class represents the Users of the OnApp installation.
 *
 * The OnApp_User class uses the following basic methods:
 * {@link load}, {@link save}, {@link delete}, and {@link getList}.
 *
 * For full fields reference and curl request details visit: ( http://help.onapp.com/manual.php?m=17 )
 */
class OnApp_User extends OnApp {
	/**
	 * @property int        $id                            ID
	 * @property int        $used_cpu_shares
	 * @property int        $used_cpus
	 * @property int        $used_disk_size
	 * @property int        $used_memory
	 * @property int        $memory_available
	 * @property int        $disk_space_available
	 * @property int        $billing_plan_id
	 * @property int        $image_template_group_id
	 * @property int        $user_group_id
	 * @property int        $aflexi_user_id
	 * @property string        $email                        email
	 * @property string        $first_name                    first name
	 * @property string        $last_name                    last name
	 * @property string        $login                        login
	 * @property string        $activated_at                activation date
	 * @property string        $created_at                    creation date
	 * @property string        $deleted_at                    deletion date
	 * @property string        $updated_at                    updating date
	 * @property string        $suspend_at                    suspension date
	 * @property string        $time_zone                    time zone
	 * @property string        $status                        status
	 * @property string        $locale                        locale
	 * @property string        $aflexi_username
	 * @property string        $aflexi_key
	 * @property string        $cdn_status
	 * @property string        $cdn_account_status
	 * @property string        $aflexi_password
	 * @property string        $remember_token
	 * @property string        $remember_token_expires_at
	 * @property float        $outstanding_amount            outstanding amount
	 * @property float        $payment_amount                payment amount
	 * @property float        $total_amount                total amount
	 * @property array        $roles
	 * @property array        $used_ip_addresses
	 * @property array        $additional_fields
	 * @property boolean     $update_billing_stat
	 */

	/**
	 * root tag used in the API request
	 *
	 * @var string
	 */
	protected $_tagRoot = 'user';

	/**
	 * alias processing the object data
	 *
	 * @var string
	 */
	protected $_resource = 'users';

	public static $nestedData = array(
		'roles'             => 'Role',
		'used_ip_addresses' => 'User_UsedIpAddress',
		'additional_fields' => 'User_AdditionalField'
	);

	public function __construct() {
		parent::__construct();
		$this->className = __CLASS__;
	}

	/**
	 * Returns the URL Alias of the API Class that inherits the OnApp class
	 *
	 * @param string $action action name
	 *
	 * @return string API resource
	 * @access public
	 */
	protected function getResource( $action = ONAPP_GETRESOURCE_DEFAULT ) {
		switch( $action ) {
			case ONAPP_GETRESOURCE_NETWORKS_LIST_BY_GROUP_ID:
				/**
				 * ROUTE :
				 * @name user_group_users
				 * @method GET
				 * @alias  /user_groups/:user_group_id/users(.:format)
				 * @format  {:controller=>"users", :action=>"index"}
				 */
				$resource = 'user_groups/' . $this->_user_group_id . '/' . $this->_resource;
				break;
			case ONAPP_GETRESOURCE_SUSPEND_USER:
				$resource = $this->getResource( ONAPP_GETRESOURCE_LOAD ) . '/suspend';
				break;

			case ONAPP_GETRESOURCE_DELETE_USER:
				$resource = $this->getResource( ONAPP_GETRESOURCE_LOAD );
				break;

			case ONAPP_GETRESOURCE_ACTIVATE:
				$resource = $this->getResource( ONAPP_GETRESOURCE_LOAD ) . '/activate';
				break;

			default:
				$resource = parent::getResource( $action );
				break;
		}

		$actions = array(
			ONAPP_GETRESOURCE_SUSPEND_USER,
			ONAPP_GETRESOURCE_ACTIVATE,
		);

		if( in_array( $action, $actions ) ) {
			$this->logger->debug( 'getResource( ' . $action . ' ): return ' . $resource );
		}

		return $resource;
	}

	/**
	 * Suspend User
	 *
	 * @access public
	 */
	public function suspend() {
		$this->setAPIResource( $this->getResource( ONAPP_GETRESOURCE_SUSPEND_USER ) );

		$response = $this->sendRequest( ONAPP_REQUEST_METHOD_POST );

		$result = $this->_castResponseToClass( $response );

		$this->_obj = $result;
	}

	/**
	 * Activate User
	 *
	 * @access public
	 */
	public function activate_user() {
		$this->setAPIResource( $this->getResource( ONAPP_GETRESOURCE_ACTIVATE ) );

		$response = $this->sendRequest( ONAPP_REQUEST_METHOD_POST );

		$result = $this->_castResponseToClass( $response );

		$this->_obj = $result;
	}

	/**
	 * Alias for activate_user method
	 */
	public function unsuspend() {
		$this->activate_user();
	}

	/**
	 * Save Object in to your account.
	 */
	public function save() {
		$this->role_ids = $this->fillRolesIDs();

		if( is_null( $this->id ) ) {
			$obj = $this->_create();
		}
		else {
			$obj = $this->_edit();
		}
		unset( $this->password, $this->password_confirmation );

		if( isset( $obj ) && ! isset( $obj->errors ) ) {
			$this->load( $obj->id );
		}
	}

	public function load( $id = null ) {
		$result = parent::load( $id );
		return $result;
	}

	/**
	 * Gets list of users by group id
	 *
	 * @param integer|null $group_id user group id
	 *
	 * @return bool|mixed
	 */
	public function getListByGroupId( $group_id = null ) {
		if( $group_id ) {
			$this->_user_group_id = $group_id;
		}
		else {
			$this->logger->error(
				'getListByHypervisorGroupId: argument _hypervisor_group_id not set.',
				__FILE__,
				__LINE__
			);
		}

		$this->setAPIResource( $this->getResource( ONAPP_GETRESOURCE_NETWORKS_LIST_BY_GROUP_ID ) );

		$response = $this->sendRequest( ONAPP_REQUEST_METHOD_GET );

		if( ! empty( $response[ 'errors' ] ) ) {
			$this->errors = $response[ 'errors' ];
			return false;
		}

		$result     = $this->castStringToClass( $response );
		$this->_obj = $result;

		return ( is_array( $result ) || ! $result ) ? $result : array( $result );
	}

	/**
	 * Deletes User from OnApp CP
	 *
	 * @param boolean $force whether to delete completely
	 */
	public function delete( $force = false ) {
		if( ! $this->_id ) {
			$this->logger->error(
				'DeleteUser: argument _id not set.',
				__FILE__,
				__LINE__
			);
		}

		if( $force ) {
			$data = array(
				'root' => 'tmp_holder',
				'data' => array(
					'force' => '1'
				)
			);

			$this->sendDelete( ONAPP_GETRESOURCE_DELETE_USER, $data );
		}
		else {
			parent::delete();
		}
	}

	private function fillRolesIDs() {
		if( is_null( $this->role_ids ) ) {
			$ids = array();
			if( ! is_null( $this->roles ) ) {
				$data = $this->roles;
			}
			elseif( isset( $this->_obj->roles ) && ! is_null( $this->_obj->roles ) ) {
				$data = $this->_obj->roles;
			}
			else {
				return null;
			}

			foreach( $data as $role ) {
				$ids[ ] = $role->id;
			}

			return $ids;
		}
		else {
			return $this->role_ids;
		}
	}
}