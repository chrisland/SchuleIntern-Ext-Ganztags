<?php

class setDateAktivity extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['write'] !== 1 && (int)DB::getSession()->isMember($this->extension['adminGroupName']) !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $arr = (object)[
            'date' => $input['date'],
            'title' => $input['title'],
            'type' => $input['type'],
            'leader_id' => (int)$input['leader'],
            'group_id' => (int)$input['group'],
            'duration' => (int)$input['duration']
        ];



        if (!$arr->leader_id) {
            return [
                'error' => true,
                'msg' => 'Missing leaderID'
            ];
        }

        if (!$arr->date) {
            return [
                'error' => true,
                'msg' => 'Missing date'
            ];
        }

        if ($arr->group_id) {
            include_once PATH_EXTENSION . 'models' . DS . 'Groups.class.php';
            $group = extGanztagsModelGroups::getByID($arr->group_id);
            $arr->color = $group->getColor();
            $arr->info = $group->getInfo();
            $arr->room = $group->getRoom();
            $arr->duration = $group->getDuration();
        }

/*
        $aktivity_id = $input['aktivity'];
        if (!$aktivity_id) {
            return [
                'error' => true,
                'msg' => 'Missing aktivityID'
            ];
        }
        */
        include_once PATH_EXTENSION . 'models' . DS . 'Day.class.php';



        if ( !extGanztagsModelDay::setItem($arr) ) {
            return [
                'error' => true,
                'msg' => 'Fehler beim Speichern!'
            ];
        }

        $ret = [];

        /*
        $data = extGanztagsModelGroups::getAll();


        if (count($data) > 0) {
            foreach ($data as $item) {

                $ret[] = $item->getCollection();
            }
        }

        */


        return $ret;

	}


	/**
	 * Set Allowed Request Method
	 * (GET, POST, ...)
	 * 
	 * @return String
	 */
	public function getAllowedMethod() {
		return 'POST';
	}


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth() {
        return true;
    }

    /**
     * Ist eine Admin berechtigung n??tig?
     * only if : needsUserAuth = true
     * @return Boolean
     */
    public function needsAdminAuth()
    {
        return false;
    }
    /**
     * Ist eine System Authentifizierung n??tig? (mit API key)
     * only if : needsUserAuth = false
     * @return Boolean
     */
    public function needsSystemAuth() {
        return false;
    }

}	

?>