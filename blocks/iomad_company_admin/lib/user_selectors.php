<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once(dirname(__FILE__) . '/../../../enrol/locallib.php');

/**
 * base class for selecting users of a company
 */
abstract class company_user_selector_base extends user_selector_base {
    const MAX_USERS_PER_PAGE = 100;

    protected $companyid;
    protected $courseid;
    protected $departmentid;

    public function __construct($name, $options) {
        $this->companyid  = $options['companyid'];
        if (isset ( $options['courseid']) ) {
            $this->courseid = $options['courseid'];
        }
        if (empty($options['departmentid'])) {
            $parentdepartment = company::get_company_parentnode($this->companyid);
            $this->departmentid = $parentdepartment->id;
        } else {
            $this->departmentid = $options['departmentid'];
        }
        parent::__construct($name, $options);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['companyid'] = $this->companyid;
        $options['file']    = 'blocks/iomad_company_admin/lib.php';
        return $options;
    }

    protected function get_course_user_ids() {
        global $DB, $PAGE;
        if (!isset( $this->courseid) ) {
            return array();
        } else {
            $course = $DB->get_record('course', array('id' => $this->courseid));
            $courseenrolmentmanager = new courseenrolmentmanager($PAGE, $course);

            $users = $courseenrolmentmanager->get_users('lastname', $sort = 'ASC', $page = 0, $perpage = 0);

            // Only return the keys (user ids).
            return array_keys($users);
        }
    }
}

class current_company_managers_user_selector extends company_user_selector_base {
    /**
     * Company manager users
     * @param <type> $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['companyid'] = $this->companyid;

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {user} u
                JOIN {company_users} cu ON (u.id = cu.userid AND cu.companyid = :companyid)
                WHERE $wherecondition ";

        $order = ' ORDER BY u.lastname ASC, u.firstname ASC';

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > company_user_selector_base::MAX_USERS_PER_PAGE) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('companymanagersmatching', 'block_iomad_company_admin', $search);
        } else {
            $groupname = get_string('companymanagers', 'block_iomad_company_admin');
        }

        return array($groupname => $availableusers);
    }
}


class potential_company_managers_user_selector extends company_user_selector_base {
    /**
     * Potential company manager users
     * @param <type> $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['companyid'] = $this->companyid;
        $params['companyidforjoin'] = $this->companyid;

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM
	                {user} u INNER JOIN {company_users} cu ON (cu.userid = u.id AND cu.companyid = :companyid AND cu.managertype = 0)
                WHERE $wherecondition";

        $order = ' ORDER BY u.lastname ASC, u.firstname ASC';

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > company_user_selector_base::MAX_USERS_PER_PAGE) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('potmanagersmatching', 'block_iomad_company_admin', $search);
        } else {
            $groupname = get_string('potmanagers', 'block_iomad_company_admin');
        }

        return array($groupname => $availableusers);
    }
}

class current_company_users_user_selector extends company_user_selector_base {
    /**
     * Company users
     * @param <type> $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['companyid'] = $this->companyid;

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM
	                {user} u INNER JOIN {company_users} cu ON (cu.companyid = :companyid AND cu.userid = u.id )
                WHERE $wherecondition ";

        $order = ' ORDER BY u.lastname ASC, u.firstname ASC';

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > company_user_selector_base::MAX_USERS_PER_PAGE) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('companyusersmatching', 'block_iomad_company_admin', $search);
        } else {
            $groupname = get_string('companyusers', 'block_iomad_company_admin');
        }

        return array($groupname => $availableusers);
    }
}


class potential_company_users_user_selector extends company_user_selector_base {
    /**
     * Potential company users - only shows those users that aren't already assigned to a company
     * @param <type> $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['companyid'] = $this->companyid;
        $params['companyidforjoin'] = $this->companyid;

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM
                    {user} u
                WHERE $wherecondition
                      AND u.id NOT IN (
                        SELECT userid
                        FROM
                            {company_users} )";

        $order = ' ORDER BY u.lastname ASC, u.firstname ASC';

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > company_user_selector_base::MAX_USERS_PER_PAGE) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('potusersmatching', 'block_iomad_company_admin', $search);
        } else {
            $groupname = get_string('potusers', 'block_iomad_company_admin');
        }

        return array($groupname => $availableusers);
    }
}

class current_company_course_user_selector extends company_user_selector_base {
    /**
     * Company users enrolled into the selected company course
     * @param <type> $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['companyid'] = $this->companyid;
        $params['courseid'] = $this->courseid;

        // Deal with departments.
        $departmentlist = company::get_all_subdepartments($this->departmentid);
        $departmentsql = "";
        if (!empty($departmentlist)) {
            $departmentsql = " AND cu.departmentid in (".implode(',', array_keys($departmentlist)).")";
        }

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM
	                {user} u INNER JOIN {company_users} cu
	                ON cu.userid = u.id AND managertype = 0 $departmentsql
                WHERE $wherecondition
                    AND cu.companyid = :companyid
                    AND cu.userid IN
                     (SELECT DISTINCT(ue.userid)
                     FROM {user_enrolments} ue
                     INNER JOIN {enrol} e
                     ON ue.enrolid=e.id
                     WHERE e.courseid=:courseid
                     AND ".$DB->sql_compare_text('e.enrol')."='manual')";

        $order = ' ORDER BY u.lastname ASC, u.firstname ASC';

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > company_user_selector_base::MAX_USERS_PER_PAGE) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('currentlyenrolledusersmatching', 'block_iomad_company_admin', $search);
        } else {
            $groupname = get_string('currentlyenrolledusers', 'block_iomad_company_admin');
        }

        return array($groupname => $availableusers);
    }
}

class potential_company_course_user_selector extends company_user_selector_base {
    /**
     * Company users enrolled into the selected company course
     * @param <type> $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['companyid'] = $this->companyid;
        $params['courseid'] = $this->courseid;

        // Deal with departments.
        $departmentlist = company::get_all_subdepartments($this->departmentid);
        $departmentsql = "";
        if (!empty($departmentlist)) {
            $departmentsql = " AND cu.departmentid IN (".implode(',', array_keys($departmentlist)).")";
        } else {
            $departmentsql = "";
        }

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM
	                {user} u INNER JOIN {company_users} cu ON cu.userid = u.id
                WHERE $wherecondition $departmentsql
                    AND
                    cu.companyid = :companyid
                    AND u.id NOT IN
                     (SELECT DISTINCT(ue.userid)
                     FROM {user_enrolments} ue
                     INNER JOIN {enrol} e
                     ON ue.enrolid=e.id
                     WHERE e.courseid=:courseid
                     AND ".$DB->sql_compare_text('e.enrol')."='manual')";

        $order = ' ORDER BY u.lastname ASC, u.firstname ASC';

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > company_user_selector_base::MAX_USERS_PER_PAGE) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('potentialcourseusersmatching', 'block_iomad_company_admin', $search);
        } else {
            $groupname = get_string('potentialcourseusers', 'block_iomad_company_admin');
        }

        return array($groupname => $availableusers);
    }
}

class potential_department_user_selector extends user_selector_base {
    /**
     * Company users enrolled into the selected company course
     * @param <type> $search
     * @return array
     */
    const MAX_USERS_PER_PAGE = 100;

    protected $companyid;
    protected $departmentid;
    protected $roletype;
    protected $parentdepartmentid;

    public function __construct($name, $options) {
        $this->companyid  = $options['companyid'];
        $this->departmentid = $options['departmentid'];
        $this->roletype = $options['roletype'];
        $this->subdepartments = $options['subdepartments'];
        $this->parentdepartmentid = $options['parentdepartmentid'];
        parent::__construct($name, $options);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['companyid'] = $this->companyid;
        $options['departmentid'] = $this->departmentid;
        $options['roletype'] = $this->roletype;
        $options['subdepartments'] = $this->subdepartments;
        $options['parentdepartmentid'] = $this->parentdepartmentid;
        $options['file']    = 'blocks/iomad_company_admin/lib.php';
        return $options;
    }

    protected function get_department_user_ids() {
        global $DB;
        if (!isset( $this->departmentid) ) {
            return array();
        } else {
            if ($users = $DB->get_records('company_users', array('departmentid' => $this->departmentid,
                                                                 'managertype' => $this->roletype), null, 'userid')) {
                // Only return the keys (user ids).
                return array_keys($users);
            } else {
                return array();
            }
        }
    }

    public function find_users($search) {
        global $DB, $USER;

        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['companyid'] = $this->companyid;

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $departmentusers = $this->get_department_user_ids();
        if (count($departmentusers) > 0) {
            $departmentuserids = $USER->id.','.implode(',', $departmentusers);
            if ($departmentuserids != ',') {
                $userfilter = " AND NOT u.id in ($departmentuserids) ";
            } else {
                $userfilter = "";
            }
        } else {
            $userfilter = "";
        }
        if ($this->roletype != 0) {
            // Dealing with management possibles could be from anywhere.
            $deptids = implode(',', array_keys($this->subdepartments));
        } else {
            // Normal staff allocations.
            unset($this->subdepartments[$this->departmentid]);
            if ($this->departmentid == $this->parentdepartmentid->id) {
                $deptids = implode(',', array_keys($this->subdepartments));
            } else {
                if (!empty($this->subdepartments)) {
                    $deptids = $this->parentdepartmentid->id .','.implode(',', array_keys($this->subdepartments));
                } else {
                    $deptids = $this->parentdepartmentid->id;
                }
            }
        }

        if (!empty($deptids)) {
            $departmentsql = "AND du.departmentid in ($deptids)";
        } else {
            return array();
        }

        $sql = " FROM
                    {user} u
                    INNER JOIN {company_users} du ON du.userid = u.id
                WHERE $wherecondition
                    $departmentsql
                    $userfilter";

        $order = ' ORDER BY u.lastname ASC, u.firstname ASC';

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > company_user_selector_base::MAX_USERS_PER_PAGE) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }
        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            if ($this->roletype != 0) {
                $groupname = get_string('potmanagersmatching', 'block_iomad_company_admin', $search);
            } else {
                $groupname = get_string('potusersmatching', 'block_iomad_company_admin', $search);
            }
        } else {
            if ($this->roletype != 0) {
                $groupname = get_string('potmanagers', 'block_iomad_company_admin');
            } else {
                $groupname = get_string('potusers', 'block_iomad_company_admin');
            }
        }

        return array($groupname => $availableusers);
    }
}

class current_department_user_selector extends user_selector_base {
    /**
     * Company users enrolled into the selected company course
     * @param <type> $search
     * @return array
     */
    const MAX_USERS_PER_PAGE = 100;

    protected $companyid;
    protected $departmentid;
    protected $roletype;

    public function __construct($name, $options) {
        $this->companyid  = $options['companyid'];
        $this->departmentid = $options['departmentid'];
        $this->roletype = $options['roletype'];
        parent::__construct($name, $options);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['companyid'] = $this->companyid;
        $options['departmentid'] = $this->departmentid;
        $options['roletype'] = $this->roletype;
        $options['file']    = 'blocks/iomad_company_admin/lib.php';
        return $options;
    }

    protected function get_department_user_ids() {
        global $DB;
        if (!isset( $this->departmentid) ) {
            return array();
        } else {
            if ($users = $DB->get_records('company_users', array('departmentid' => $this->departmentid), null, 'userid')) {
                // Only return the keys (user ids).
                return array_values($users);
            } else {
                return array();
            }
        }
    }

    public function find_users($search) {
        global $DB, $USER;

        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['companyid'] = $this->companyid;

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM
                    {user} u
                    INNER JOIN {company_users} cu ON cu.userid = u.id
                WHERE $wherecondition
                    AND cu.managertype = ($this->roletype)
                    AND
                    u.id != ($USER->id)
                    AND
                    cu.departmentid = ($this->departmentid)";

        $order = ' ORDER BY u.lastname ASC, u.firstname ASC';

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > company_user_selector_base::MAX_USERS_PER_PAGE) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }
        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            if ($this->roletype == 2) {
                $groupname = get_string('departmentmanagersmatching', 'block_iomad_company_admin', $search);
            } else if ($this->roletype == 0) {
                $groupname = get_string('departmentusersmatching', 'block_iomad_company_admin', $search);
            } else if ($this->roletype == 1) {
                $groupname = get_string('companymanagersmatching', 'block_iomad_company_admin', $search);
            }
        } else {
            if ($this->roletype == 2) {
                $groupname = get_string('departmentmanagers', 'block_iomad_company_admin');
            } else if ($this->roletype == 0) {
                $groupname = get_string('departmentusers', 'block_iomad_company_admin');
            } else if ($this->roletype == 1) {
                $groupname = get_string('companymanagers', 'block_iomad_company_admin');
            }
        }

        return array($groupname => $availableusers);
    }
}

class potential_license_user_selector extends user_selector_base {
    /**
     * Company users enrolled into the selected company course
     * @param <type> $search
     * @return array
     */
    const MAX_USERS_PER_PAGE = 100;

    protected $companyid;
    protected $licenseid;
    protected $departmentid;
    protected $subdepartments;
    protected $parentdepartmentid;

    public function __construct($name, $options) {
        $this->companyid  = $options['companyid'];
        $this->licenseid = $options['licenseid'];
        $this->departmentid = $options['departmentid'];
        $this->subdepartments = $options['subdepartments'];
        $this->parentdepartmentid = $options['parentdepartmentid'];
        parent::__construct($name, $options);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['companyid'] = $this->companyid;
        $options['licenseid'] = $this->licenseid;
        $options['departmentid'] = $this->departmentid;
        $options['subdepartments'] = $this->subdepartments;
        $options['parentdepartmentid'] = $this->parentdepartmentid;
        $options['file']    = 'blocks/iomad_company_admin/lib.php';
        return $options;
    }

    protected function get_license_user_ids() {
        global $DB;
        if (!isset( $this->licenseid) ) {
            return array();
        } else {
            $usersql = "select DISTINCT userid from {companylicense_users} where licenseid=".$this->licenseid." and id not in
            (SELECT id from {companylicense_users}
            WHERE licenseid = ".$this->licenseid."
            AND timecompleted IS NOT NULL)";;
            if ($users = $DB->get_records_sql($usersql)) {
                // Only return the keys (user ids).
                return array_keys($users);
            } else {
                return array();
            }
        }
    }

    protected function get_license_department_ids() {
        global $DB, $USER;
        if (!isset( $this->licenseid) ) {
            return array();
        } else {
            if (!$DB->get_record_sql("SELECT pc.id
                                      FROM {iomad_courses} pc
                                      INNER JOIN {companylicense_courses} clc
                                      ON clc.courseid = pc.courseid
                                      WHERE clc.licenseid=$this->licenseid
                                      AND pc.shared=1")) {
                // Check if we are a shared course or not.
                $courses = $DB->get_records('companylicense_courses', array('licenseid' => $this->licenseid));
                $shared = false;
                foreach ($courses as $course) {
                    if ($DB->get_record_select('iomad_courses', "courseid='".$course->courseid."' AND shared!= 0")) {
                        $shared = true;
                    }
                }
                $sql = "SELECT DISTINCT d.id from {department} d, {company_course} cc, {companylicense_courses} clc
                        WHERE
                        d.id = cc.departmentid
                        AND
                        cc.courseid = clc.courseid
                        AND
                        clc.licenseid = ".$this->licenseid ."
                        AND d.company = ".$this->companyid;
                $departments = $DB->get_records_sql($sql);
                $shareddepartment = array();
                if ($shared) {
                    if (has_capability('block/iomad_company_admin:edit_licenses', context_system::instance())) {
                        // Need to add the top level department.
                        $shareddepartment = company::get_company_parentnode($this->companyid);
                        $departments = $departments + array($shareddepartment->id => $shareddepartment->id);
                    } else {
                        $shareddepartment = company::get_userlevel($USER);
                        $departments = $departments + array($shareddepartment->id => $shareddepartment->id);
                    }
                }
                if (!empty($departments)) {
                    // Only return the keys (user ids).
                    return array_keys($departments);
                } else {
                    return array();
                }
            } else {
                return array($this->departmentid);
            }
        }
    }

    protected function process_license_allocations(&$licenseusers) {
        global $DB;
        foreach ($licenseusers as $id => $user) {

            $sql = "SELECT d.shortname FROM {department} d
                    INNER JOIN {company_users} cu ON cu.departmentid = d.id
                    WHERE
                    cu.userid = $id";
            if ($department = $DB->get_record_sql($sql)) {
                $licenseusers[$id]->email = $user->email." (".$department->shortname.")";
            }
        }
    }

    public function find_users($search) {
        global $DB, $USER;

        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['companyid'] = $this->companyid;

        $fields      = 'SELECT ' . $this->required_fields_sql('u').', u.email, d.shortname ';
        $countfields = 'SELECT COUNT(1)';

        $licenseusers = $this->get_license_user_ids();
        if (count($licenseusers) > 0) {
            $licenseuserids = implode(',', $licenseusers);
            if ($licenseuserids != ',') {
                $userfilter = " AND NOT u.id in ($licenseuserids) ";
            } else {
                $userfilter = "";
            }
        } else {
            $userfilter = "";
        }

        // Get the department ids for this license.
        $departmentids = $this->get_license_department_ids();
        // Add subdepartments to include their users.
        foreach ($departmentids as $deptid) {
            $departmentids += array_keys(company::get_all_subdepartments($deptid));
        }
        $deptids = implode(',', $departmentids);

        if (!empty($deptids)) {
            $departmentsql = "AND du.departmentid in ($deptids)";
        } else {
            return array();
        }

        $sql = " FROM
                    {user} u
                    INNER JOIN {company_users} du ON du.userid = u.id
                    INNER JOIN {department} d ON d.id = du.departmentid
                WHERE $wherecondition
                    $departmentsql
                    $userfilter";

        $order = ' ORDER BY u.lastname ASC, u.firstname ASC';

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > company_user_selector_base::MAX_USERS_PER_PAGE) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }
        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);

        if (empty($availableusers)) {
            return array();
        }

        $this->process_license_allocations($availableusers);
        if ($search) {
            $groupname = get_string('potusersmatching', 'block_iomad_company_admin', $search);
        } else {
            $groupname = get_string('potusers', 'block_iomad_company_admin');
        }

        return array($groupname => $availableusers);
    }
}

class current_license_user_selector extends user_selector_base {
    /**
     * Company users enrolled into the selected company course
     * @param <type> $search
     * @return array
     */
    const MAX_USERS_PER_PAGE = 100;

    protected $companyid;
    protected $licenseid;
    protected $departmentid;
    protected $subdepartments;
    protected $parentdepartmentid;

    public function __construct($name, $options) {
        $this->companyid  = $options['companyid'];
        $this->licenseid = $options['licenseid'];
        $this->departmentid = $options['departmentid'];
        $this->subdepartments = $options['subdepartments'];
        $this->parentdepartmentid = $options['parentdepartmentid'];
        parent::__construct($name, $options);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['companyid'] = $this->companyid;
        $options['licenseid'] = $this->licenseid;
        $options['departmentid'] = $this->departmentid;
        $options['subdepartments'] = $this->subdepartments;
        $options['parentdepartmentid'] = $this->parentdepartmentid;
        $options['file']    = 'blocks/iomad_company_admin/lib.php';
        return $options;
    }

    protected function get_license_user_ids() {
        global $DB;
        if (!isset( $this->licenseid) ) {
            return array();
        } else {
            $usersql = "select DISTINCT userid from {companylicense_users} where licenseid=".$this->licenseid." and id not in
            (SELECT id from {companylicense_users}
            WHERE licenseid = ".$this->licenseid."
            AND timecompleted IS NOT NULL)";
            if ($users = $DB->get_records_sql($usersql)) {
                // Only return the keys (user ids).
                return array_values($users);
            } else {
                return array();
            }
        }
    }

    protected function process_license_allocations(&$licenseusers) {
        global $DB;
        foreach ($licenseusers as $id => $user) {
            $sql = "SELECT d.shortname from {department} d
                    INNER JOIN {company_users} cu ON cu.departmentid = d.id
                    WHERE
                    cu.userid = $id";
            if ($department = $DB->get_record_sql($sql)) {
                $licenseusers[$id]->email = $user->email." (".$department->shortname.")";
            }
            if ($licenseinfo = $DB->get_record('companylicense_users', array('userid' => $id,
                                                                             'licenseid' => $this->licenseid,
                                                                             'timecompleted' => null))) {
                if ($licenseinfo->isusing == 1) {
                    $licenseusers[$id]->firstname = '*'.$user->firstname;
                }
            }
        }
    }

    public function find_users($search) {
        global $DB, $USER;

        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, '');
        $params['companyid'] = $this->companyid;

        $licenseusers = $this->get_license_user_ids();
        $licenseuserids = "";
        if (count($licenseusers) > 0) {
            foreach ($licenseusers as $licenseuser) {
                if (!empty($licenseuserids)) {
                    $licenseuserids .= ','.$licenseuser->userid;
                } else {
                    $licenseuserids = $licenseuser->userid;
                }
            }
            if ($licenseuserids != ',') {
                $userfilter = $licenseuserids;
            } else {
                $userfilter = "";
            }
        } else {
            $userfilter = "";
        }

        $fields      = 'SELECT ' . $this->required_fields_sql('');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM
                 {user}
                 WHERE $wherecondition
                     AND id in (".$userfilter.") ";

        $order = ' ORDER BY lastname ASC, firstname ASC';

        if (!$this->is_validating()) {
            if (!empty($userfilter)) {
                $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
                if ($potentialmemberscount > company_user_selector_base::MAX_USERS_PER_PAGE) {
                    return $this->too_many_results($search, $potentialmemberscount);
                }
            } else {
                $potentialmemberscount = 0;
            }
        }
        if (!empty($userfilter)) {
            $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);
        } else {
            $availableusers = array();
        }
        if (empty($availableusers)) {
            return array();
        }

        $this->process_license_allocations($availableusers);

        if ($search) {
            $groupname = get_string('licenseusersmatching', 'block_iomad_company_admin', $search);
        } else {
            $groupname = get_string('licenseusers', 'block_iomad_company_admin');
        }

        return array($groupname => $availableusers);
    }
}
