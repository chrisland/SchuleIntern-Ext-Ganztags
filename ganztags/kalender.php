<?php

class extGanztagsKalender extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fas fa fa-users"></i> Ganztags - Kalender';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }


    public function execute()
    {

        //$_request = $this->getRequest();
        //print_r($_request);


        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->isMember($this->getAdminGroup()) !== 1) {
            new errorPage('Kein Zugriff');
        }
        //print_r( $acl );

        $user = DB::getSession()->getUser();

        $showDays = array(
            'Mo' => DB::getSettings()->getValue("ext_ganztags-day-mo"),
            'Di' => DB::getSettings()->getValue("ext_ganztags-day-di"),
            'Mi' => DB::getSettings()->getValue("ext_ganztags-day-mi"),
            'Do' => DB::getSettings()->getValue("ext_ganztags-day-do"),
            'Fr' => DB::getSettings()->getValue("ext_ganztags-day-fr"),
            'Sa' => DB::getSettings()->getValue("ext_ganztags-day-sa"),
            'So' => DB::getSettings()->getValue("ext_ganztags-day-so")
        );

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/kalender/dist/main.js'
            ],
            "data" => [
                "apiURL" => "rest.php/ganztags",
                "acl" => $acl['rights'],
                "showDays" => $showDays
            ]
        ]);

    }

    public function taskPrintDay($postData)
    {

        if (!$postData['day']) {
            return false;
        }

        $date = $postData['day'];

        $day = date('D',strtotime($date));
        $days_arr = ['Mon' => 'mo','Tue' => 'di','Wed' => 'mi','Thu' => 'do','Fri' => 'fr','Sat' => 'sa','Son' => 'so'];
        $day = $days_arr[$day];

        $pdf = new PrintNormalPageA4WithoutHeader('Ganztags');
        $pdf->setPrintedDateInFooter();

        //include_once PATH_EXTENSION . 'models' . DS . 'Groups.class.php';
        //$groups = extGanztagsModelGroups::getAll();

        include_once PATH_EXTENSION . 'models' . DS . 'Day.class.php';
        $groups = extGanztagsModelDay::getByDate($date);

        include_once("../framework/lib/data/absenzen/Absenz.class.php");

        $absenzen = Absenz::getAbsenzenForDate($date, null);



        foreach ($groups as $group) {

            $group->getSchueler($day);
            $group_data = $group->getCollection(true);


            if ($group_data['type'] == 'day-group') {
                $html = '';
                $html .= '<style>
					table {
						width: 100%;
					}
					td { padding: 0.3rem; }
					</style>';
                $html .= '<h3 style="text-align: right">' . $date . '</h3>';
                $html .= '<h1>' . $group_data['title'] . '</h1>';
                $html .= '<h4 style="color:#ccc">' . $group_data['leader']['name'].' - '.$group_data['room'] . '</h4>';
                $html .= '<table cellspacing="0" cellpadding="5" border="0" style="border-color:white; border-collapse: collapse;" >
						<thead >
							<tr>
								<th width="5%"></th>
								<th width="15%" style="font-weight: bold;">Vorname</th>
								<th width="18%" style="font-weight: bold;">Name</th>
								<th width="5%" style="font-weight: bold;"></th>
								<th width="8%" style="font-weight: bold;"></th>
								<th width="6%" style="font-weight: bold;"><img src="./images/check-circle.svg" height="12px" width="12px"/></th>
								<th width="6%" style="font-weight: bold;"><img src="./images/times-circle.svg" height="12px" width="12px"/></th>
								<th width="" style="font-weight: bold;">Info</th>
							</tr>
						</thead>
						<tbody>';

                if ($group_data['schueler']) {
                    $num = 1;
                    foreach ($group_data['schueler'] as $schueler) {
                        if ($schueler['days'] && $schueler['days'][$day]) {

                            $isAbsenz = false;

                            if ($schueler['days'][$day]->info) {
                                if ($schueler['info']) {
                                    $schueler['info'] .= '<br>';
                                }
                                $schueler['info'] .= $schueler['days'][$day]->info;
                            }
                            foreach($absenzen as $absenz) {
                                if  ( $schueler['asvid'] == $absenz->getSchueler()->getAsvID() ) {
                                    $isAbsenz = true;
                                    if ($schueler['info']) {
                                        $schueler['info'] .= '<br>';
                                    }
                                    $schueler['info'] .= '<b>Absenz:</b> '.$absenz->getStundenAsString().' Stunde<br><i>'.nl2br($absenz->getBemerkung()).'</i> '.nl2br($absenz->getGanztagsNotiz());
                                }
                            }

                            $style = '';
                            $boder = 'border-right: 0.01px solid #ccc;';
                            if ($num%2) {
                                $style = 'background-color: rgb(236, 240, 245); margin: 30px;';
                                $boder = 'border-right: 0.01px solid white';
                            }
                            $html .= '<tr style="'.$style.'">';
                            $html .= '<td width="5%" style="color:#ccc">'.$num.'</td>';

                            $html .= '<td width="15%">'.$schueler['vorname'].'</td>';
                            $html .= '<td width="18%">'.$schueler['nachname'].'</td>';

                            $html .= '<td width="5%">';
                            if ($schueler['gender'] == 'm') {
                                $html .= '<img src="./images/mars.svg" height="10px" width="10px"/>';
                            } else if ($schueler['gender'] == 'w') {
                                $html .= '<img src="./images/venus.svg" height="10px" width="10px"/>';
                            }
                            $html .= '</td>';
                            $html .= '<td width="8%" style="'.$boder.'">'.$schueler['klasse'].'</td>';

                            $html .= '<td width="6%" style="'.$boder.'"></td>';
                            if ($isAbsenz) {
                                $html .= '<td width="6%" style="'.$boder.'"><img src="./images/bed.svg" width="14px" height="17px"/></td>';
                            } else {
                                $html .= '<td width="6%" style="'.$boder.'"></td>';
                            }


                            $html .= '<td width="">';
                            $html .= '<div style="font-size: 90%; text-align:right">'.$schueler['info'].'</div>';
                            $html .= '</td>';

                            $html .= '</tr>';
                            $num++;
                        }

                    }
                }
                $html .= '</tbody></table>';
                $pdf->setHTMLContent($html);


            } else if ($group_data['type'] == 'day-activity') {
                $html = '';
                $html .= '<h3 style="text-align: right">' . $date . '</h3>';
                $html .= '<h1>' . $group_data['title'] . '</h1>';
                $html .= '<h4 style="color:#ccc">' . $group_data['leader']['name'].' - '.$group_data['room'] . '</h4>';
                $pdf->setHTMLContent($html);
            }


        }
        $pdf->send();
        exit;
    }


}
