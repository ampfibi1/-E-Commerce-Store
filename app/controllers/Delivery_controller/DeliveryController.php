<?php
// ============================================================
// app/controllers/DeliveryController.php
// Role 3 – Delivery Manager
// Handles: dashboard, agents, zones, orders, assignments,
//          active deliveries, status updates, reports
// ============================================================

require_once APP . '/models/Delivery_model/DeliveryAgentModel.php';
require_once APP . '/models/Delivery_model/DeliveryZoneModel.php';
require_once APP . '/models/Delivery_model/DeliveryAssignmentModel.php';
require_once APP . '/models/Delivery_model/DeliveryReportModel.php';

class DeliveryController {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ----------------------------------------------------------
    // INDEX — default action
    // ----------------------------------------------------------
    public function index() {
        $this->dashboard();
    }

    // ==========================================================
    // DASHBOARD  ?c=delivery&a=dashboard
    // ==========================================================
    public function dashboard() {
        require_role('delivery_manager');

        // Fetch summary counts for the 3 stat cards
        $pending_dispatch = delivery_count_pending_dispatch($this->conn);
        $active_deliveries = delivery_count_active($this->conn);
        $delivered_today   = delivery_count_delivered_today($this->conn);

        $page_title = 'Dashboard';
        include APP . '/views/delivery/dashboard.php';
    }

    // ==========================================================
    // AGENTS  – CRUD
    // ==========================================================

    // List agents   ?c=delivery&a=agents
    public function agents() {
        require_role('delivery_manager');
        $agents     = delivery_agent_get_all($this->conn);
        $page_title = 'Delivery Agents';
        include APP . '/views/delivery/agents_list.php';
    }

    // Add agent form + POST   ?c=delivery&a=agent_add
    public function agent_add() {
        require_role('delivery_manager');
        $errors = array();
        $old    = array('name' => '', 'phone' => '', 'vehicle_type' => '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name         = isset($_POST['name'])         ? trim($_POST['name'])         : '';
            $phone        = isset($_POST['phone'])        ? trim($_POST['phone'])        : '';
            $vehicle_type = isset($_POST['vehicle_type']) ? trim($_POST['vehicle_type']) : '';

            $old = array('name' => $name, 'phone' => $phone, 'vehicle_type' => $vehicle_type);

            // Validation
            if ($name === '')         $errors['name']         = 'Agent name is required.';
            if ($phone === '')        $errors['phone']        = 'Phone number is required.';
            if ($vehicle_type === '') $errors['vehicle_type'] = 'Vehicle type is required.';

            if (empty($errors)) {
                delivery_agent_insert($this->conn, $name, $phone, $vehicle_type);
                set_flash('success', 'Agent added successfully.');
                redirect(BASE_URL . '?c=delivery&a=agents');
            }
        }

        $page_title = 'Add Agent';
        include APP . '/views/delivery/agent_form.php';
    }

    // Edit agent   ?c=delivery&a=agent_edit&id=N
    public function agent_edit() {
        require_role('delivery_manager');
        $id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $agent  = delivery_agent_get_by_id($this->conn, $id);
        $errors = array();

        if (!$agent) {
            set_flash('error', 'Agent not found.');
            redirect(BASE_URL . '?c=delivery&a=agents');
        }

        $old = array(
            'name'         => $agent['name'],
            'phone'        => $agent['phone'],
            'vehicle_type' => $agent['vehicle_type'],
            'status'       => $agent['status'],
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name         = isset($_POST['name'])         ? trim($_POST['name'])         : '';
            $phone        = isset($_POST['phone'])        ? trim($_POST['phone'])        : '';
            $vehicle_type = isset($_POST['vehicle_type']) ? trim($_POST['vehicle_type']) : '';
            $status       = isset($_POST['status'])       ? trim($_POST['status'])       : 'active';

            $old = array('name' => $name, 'phone' => $phone,
                         'vehicle_type' => $vehicle_type, 'status' => $status);

            if ($name === '')         $errors['name']         = 'Agent name is required.';
            if ($phone === '')        $errors['phone']        = 'Phone number is required.';
            if ($vehicle_type === '') $errors['vehicle_type'] = 'Vehicle type is required.';

            if (empty($errors)) {
                delivery_agent_update($this->conn, $id, $name, $phone, $vehicle_type, $status);
                set_flash('success', 'Agent updated successfully.');
                redirect(BASE_URL . '?c=delivery&a=agents');
            }
        }

        $page_title  = 'Edit Agent';
        $is_edit     = true;
        include APP . '/views/delivery/agent_form.php';
    }

    // Toggle agent status (activate/deactivate)   ?c=delivery&a=agent_toggle&id=N
    public function agent_toggle() {
        require_role('delivery_manager');
        $id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $agent = delivery_agent_get_by_id($this->conn, $id);

        if ($agent) {
            $new_status = ($agent['status'] === 'active') ? 'inactive' : 'active';
            delivery_agent_set_status($this->conn, $id, $new_status);
            set_flash('success', 'Agent status updated to ' . $new_status . '.');
        } else {
            set_flash('error', 'Agent not found.');
        }

        redirect(BASE_URL . '?c=delivery&a=agents');
    }

    // ==========================================================
    // ZONES  – CRUD
    // ==========================================================

    // List zones   ?c=delivery&a=zones
    public function zones() {
        require_role('delivery_manager');
        $zones      = delivery_zone_get_all($this->conn);
        $page_title = 'Delivery Zones';
        include APP . '/views/delivery/zones_list.php';
    }

    // Add zone   ?c=delivery&a=zone_add
    public function zone_add() {
        require_role('delivery_manager');
        $errors = array();
        $old    = array('zone_name' => '', 'delivery_fee' => '', 'estimated_days' => '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $zone_name      = isset($_POST['zone_name'])      ? trim($_POST['zone_name'])      : '';
            $delivery_fee   = isset($_POST['delivery_fee'])   ? trim($_POST['delivery_fee'])   : '';
            $estimated_days = isset($_POST['estimated_days']) ? trim($_POST['estimated_days']) : '';

            $old = array('zone_name' => $zone_name, 'delivery_fee' => $delivery_fee,
                         'estimated_days' => $estimated_days);

            if ($zone_name === '')                             $errors['zone_name']      = 'Zone name is required.';
            if ($delivery_fee === '' || !is_numeric($delivery_fee)) $errors['delivery_fee']   = 'Valid delivery fee is required.';
            if ($estimated_days === '' || !ctype_digit($estimated_days)) $errors['estimated_days'] = 'Valid estimated days required.';

            if (empty($errors)) {
                delivery_zone_insert($this->conn, $zone_name, (float)$delivery_fee, (int)$estimated_days);
                set_flash('success', 'Zone added successfully.');
                redirect(BASE_URL . '?c=delivery&a=zones');
            }
        }

        $page_title = 'Add Zone';
        include APP . '/views/delivery/zone_form.php';
    }

    // Edit zone   ?c=delivery&a=zone_edit&id=N
    public function zone_edit() {
        require_role('delivery_manager');
        $id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $zone = delivery_zone_get_by_id($this->conn, $id);

        if (!$zone) {
            set_flash('error', 'Zone not found.');
            redirect(BASE_URL . '?c=delivery&a=zones');
        }

        $errors = array();
        $old    = array(
            'zone_name'      => $zone['zone_name'],
            'delivery_fee'   => $zone['delivery_fee'],
            'estimated_days' => $zone['estimated_days'],
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $zone_name      = isset($_POST['zone_name'])      ? trim($_POST['zone_name'])      : '';
            $delivery_fee   = isset($_POST['delivery_fee'])   ? trim($_POST['delivery_fee'])   : '';
            $estimated_days = isset($_POST['estimated_days']) ? trim($_POST['estimated_days']) : '';

            $old = array('zone_name' => $zone_name, 'delivery_fee' => $delivery_fee,
                         'estimated_days' => $estimated_days);

            if ($zone_name === '')                                  $errors['zone_name']      = 'Zone name is required.';
            if ($delivery_fee === '' || !is_numeric($delivery_fee)) $errors['delivery_fee']   = 'Valid delivery fee is required.';
            if ($estimated_days === '' || !ctype_digit($estimated_days)) $errors['estimated_days'] = 'Valid estimated days required.';

            if (empty($errors)) {
                delivery_zone_update($this->conn, $id, $zone_name, (float)$delivery_fee, (int)$estimated_days);
                set_flash('success', 'Zone updated successfully.');
                redirect(BASE_URL . '?c=delivery&a=zones');
            }
        }

        $page_title = 'Edit Zone';
        $is_edit    = true;
        include APP . '/views/delivery/zone_form.php';
    }

    // Delete zone   ?c=delivery&a=zone_delete&id=N
    public function zone_delete() {
        require_role('delivery_manager');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        delivery_zone_delete($this->conn, $id);
        set_flash('success', 'Zone deleted.');
        redirect(BASE_URL . '?c=delivery&a=zones');
    }

    // ==========================================================
    // ORDERS – Ready for Dispatch
    // ==========================================================

    // ?c=delivery&a=dispatch
    public function dispatch() {
        require_role('delivery_manager');
        $orders     = delivery_orders_ready_for_dispatch($this->conn);
        $agents     = delivery_agent_get_active($this->conn);
        $zones      = delivery_zone_get_all($this->conn);
        $errors     = array();
        $page_title = 'Ready for Dispatch';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // The dispatch form submits a composite value "<order_id>:<seller_id>"
            // in the order_id field so the manager picks a specific seller's
            // shipment within the order, not the order as a whole.
            $raw      = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';
            $parts    = explode(':', $raw);
            $order_id  = isset($parts[0]) ? (int)$parts[0] : 0;
            $seller_id = isset($parts[1]) ? (int)$parts[1] : 0;
            $agent_id  = isset($_POST['agent_id']) ? (int)$_POST['agent_id'] : 0;
            $zone_id   = isset($_POST['zone_id'])  ? (int)$_POST['zone_id']  : 0;

            if ($order_id === 0 || $seller_id === 0) $errors['order_id'] = 'Please select an order.';
            if ($agent_id === 0) $errors['agent_id'] = 'Please select an agent.';
            if ($zone_id  === 0) $errors['zone_id']  = 'Please select a zone.';

            if (empty($errors)) {
                delivery_assignment_insert($this->conn, $order_id, $agent_id, $zone_id, $seller_id);
                set_flash('success', 'Agent assigned to order #' . $order_id . ' (seller #' . $seller_id . ') successfully.');
                redirect(BASE_URL . '?c=delivery&a=dispatch');
            }
        }

        include APP . '/views/delivery/dispatch.php';
    }

    // ==========================================================
    // ACTIVE DELIVERIES
    // ==========================================================

    // ?c=delivery&a=active
    public function active() {
        require_role('delivery_manager');
        $deliveries = delivery_active_list($this->conn);
        $page_title = 'Active Deliveries';
        include APP . '/views/delivery/active_deliveries.php';
    }

    // ==========================================================
    // STATUS UPDATE (non-AJAX fallback)
    // ?c=delivery&a=status_update&id=N (POST)
    // ==========================================================
    public function status_update() {
        require_role('delivery_manager');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . '?c=delivery&a=active');
        }

        $assignment_id  = isset($_POST['assignment_id'])  ? (int)$_POST['assignment_id']  : 0;
        $status         = isset($_POST['status'])         ? trim($_POST['status'])         : '';
        $failure_reason = isset($_POST['failure_reason']) ? trim($_POST['failure_reason']) : '';

        $valid_statuses = array('assigned','picked_up','in_transit','delivered','failed');

        if ($assignment_id > 0 && in_array($status, $valid_statuses)) {
            delivery_assignment_update_status($this->conn, $assignment_id, $status, $failure_reason);
            set_flash('success', 'Delivery status updated to: ' . $status);
        } else {
            set_flash('error', 'Invalid status update request.');
        }

        redirect(BASE_URL . '?c=delivery&a=active');
    }

    // ==========================================================
    // FAILED DELIVERIES + REASSIGN
    // ==========================================================

    // ?c=delivery&a=failed
    public function failed() {
        require_role('delivery_manager');
        $failed_deliveries = delivery_failed_list($this->conn);
        $agents            = delivery_agent_get_active($this->conn);
        $page_title        = 'Failed Deliveries';
        include APP . '/views/delivery/failed_deliveries.php';
    }

    // Reassign agent to a failed delivery   ?c=delivery&a=reassign (POST)
    public function reassign() {
        require_role('delivery_manager');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . '?c=delivery&a=failed');
        }

        $assignment_id = isset($_POST['assignment_id']) ? (int)$_POST['assignment_id'] : 0;
        $agent_id      = isset($_POST['agent_id'])      ? (int)$_POST['agent_id']      : 0;

        if ($assignment_id > 0 && $agent_id > 0) {
            delivery_assignment_reassign($this->conn, $assignment_id, $agent_id);
            set_flash('success', 'Delivery reassigned to new agent.');
        } else {
            set_flash('error', 'Invalid reassignment request.');
        }

        redirect(BASE_URL . '?c=delivery&a=failed');
    }

    // ==========================================================
    // REPORTS
    // ==========================================================

    // ?c=delivery&a=reports
    public function reports() {
        require_role('delivery_manager');

        $agent_performance = delivery_report_agent_performance($this->conn);
        $zone_performance  = delivery_report_zone_performance($this->conn);
        $daily_summary     = delivery_report_daily_summary($this->conn);
        $weekly_summary    = delivery_report_weekly_summary($this->conn);

        $page_title = 'Reports';
        include APP . '/views/delivery/reports.php';
    }
}
