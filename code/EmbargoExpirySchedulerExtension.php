<?php

/**
 * PublishScheduler SiteTree Extension
 * 
 * Adds a very simple way to schedule (Embargo/Expire) SiteTree items, 
 * basically we just add two datetimefields & check if within from canView()
 *
 * @package SoftScheduler
 * @author Michael van Schaik, partly based on Embargo/Expiry module by Simon Welsh
 * Some parts also extracted from micmania1/silverstripe-blogger
 */

class EmbargoExpirySchedulerExtension extends SiteTreeExtension
{
    
    public static $db = array(
        'Embargo' => 'SS_Datetime',
        'Expiry' => 'SS_Datetime'
    );


    /**
     * Adds EmbargoExpiry time fields to the CMS
     *
     * @param FieldSet $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        Requirements::css(SCHEDULER_DIR . "/css/cms.css");
        
        
        
//		$fields->insertBefore(
            $publishDate = DatetimeField::create("Embargo", _t("Scheduler.Embargo", "Page available from"));
//			"Content"
//		);
        $publishDate->getDateField()->setConfig('dateformat', 'dd-MM-yyyy');
        $publishDate->getDateField()->setConfig("showcalendar", true);
        $publishDate->getTimeField()->setConfig('timeformat', 'HH:mm');
        $publishDate->getTimeField()->setAttribute('placeholder', '00:00');
        //$publishDate->getTimeField()->setValue("13:00");
        $publishDate->setRightTitle(_t("Scheduler.LeaveEmptyEmbargo",
                "Leave empty to have page available right away (after publishing)"));
        
//		$fields->insertAfter(
            $unpublishDate = DatetimeField::create("Expiry", _t("Scheduler.Expiry", "Page expires on"));
//			"Embargo"
//		);
        $unpublishDate->getDateField()->setConfig('dateformat', 'dd-MM-yyyy');
        $unpublishDate->getDateField()->setConfig("showcalendar", true);
        $unpublishDate->getTimeField()->setConfig('timeformat', 'HH:mm');
        $unpublishDate->getTimeField()->setAttribute('placeholder', '00:00');
        $unpublishDate->setRightTitle(_t("Scheduler.LeaveEmptyExpire",
                "Leave empty to leave page published indefinitely"));
        
        $fields->insertBefore(ToggleCompositeField::create(
                'SoftScheduler',
                _t('SoftScheduler.Schedule', 'Schedule publishing & unpublishing of this page'),
                array( $publishDate, $unpublishDate )
            )->setHeadingLevel(4),
            'Content');
        
        
        //$fields->findOrMakeTab($tabName);
        //$fields->insertAfter(TextField::create('Test'),'Header');
//		Debug::dump($fields->fieldByName('Root.Main.Options'));
//		$optionspanel = $fields->fieldByName('Root.Main.Options');
//		$optionspanel->push(TextField::create('Test'));

//		$optbar = $fields->fieldByName('Root.Main.Options');
//		if(! $optbar){
//			$fields->addFieldsToTab('Root.Main', $optbar = RightSidebar::create('Options'));
//		}
//		$optbar->push( $publishDate );
//		$optbar->push( $unpublishDate );
//		$fields->fieldByName('Root')->setTemplate('RightSidebarInner');
    }
    
    public function onBeforeWrite()
    {
        if ($this->owner->Embargo["date"] && !$this->owner->Embargo["time"]) {
            $this->owner->Embargo["time"] = "00:00";
        }
        if ($this->owner->Expiry["date"] && !$this->owner->Expiry["time"]) {
            $this->owner->Expiry["time"] = "23:00";
        }
        parent::onBeforeWrite();
    }
    
    
    /*
     *  Show 'lozenges' for scheduled & expired
     */
    // convenience for use with partial caching
    public function publishedStatus()
    {
        if (!$this->owner->getScheduledStatus() && !$this->owner->getExpiredStatus()) {
            return true;
        }
        return false;
    }
    public function getScheduledStatus()
    {
        if (! $this->owner->isPublished()) {
            return false;
        }
        $embargo = $this->owner->dbObject("Embargo");
        //Debug::dump(($this->owner->Embargo)? true: false);
        if ($this->owner->Embargo && $embargo->InFuture()) {
            return true;
        }
        return false;
    }
    public function getExpiredStatus()
    {
        if (! $this->owner->isPublished()) {
            return false;
        }
        $expiry = $this->owner->dbObject("Expiry");
        if ($this->owner->Expiry && $expiry->InPast()) {
            return true;
        }
        return false;
    }
    
    public function updateStatusFlags(& $flags)
    {
        if ($this->owner->getScheduledStatus()) {
            $flags['status-scheduled'] = _t("Scheduler.SCHEDULED", "Scheduled");
        }
        if ($this->owner->getExpiredStatus()) {
            $flags['status-expired'] = _t("Scheduler.EXPIRED", "Expired");
        }
        return $flags;
    }
    
    /* 
     * Return nice statusses for use in Gridfields (eg. GridFieldPages module or descendants)
     */
    
    public function updateStatus(& $status, & $statusflag)
    {
        if ($this->owner->getScheduledStatus()) {
            $status = _t(
                "Scheduler.Scheduled",
                '<i class="btn-icon btn-icon-sprite btn-icon-accept_disabled"></i> Scheduled for {date}',
                "State for when a post is scheduled.",
                array(
                    "date" => $this->owner->dbObject("Embargo")->Nice()
                )
            );
        }
        if ($this->owner->getExpiredStatus()) {
            $status = _t(
                "Scheduler.Expired",
                '<i class="btn-icon btn-icon-sprite btn-icon-minus-circle_disabled"></i> Expired on {date}',
                "State for when a post is expired.",
                array(
                    "date" => $this->owner->dbObject("Expiry")->Nice()
                )
            );
        }
    }

    /**
     * Checks if a user can view the page
     *
     * The user can view the current page if:
     * - They have the VIEW_DRAFT_CONTENT permission or
     * - The current time is after the Embargo time (if set) and before the Expiry time (if set)
     *
     * @param Member $member
     * @return boolean
     */
    public function canView($member = null)
    {
        
        // if CMS user with sufficient rights:
        if (Permission::check("VIEW_DRAFT_CONTENT")) {
            //if(Permission::checkMember($member, 'VIEW_EMBARGOEXPIRY')) {
            return true;
        }
//		Debug::dump($this->owner->URLSegment." "
//				. $this->owner->Embargo . " "
//				. date('d-M-Y h:i', strtotime($this->owner->Embargo))
//				. " - " . $this->owner->Expiry);

        // if on front, controller should be a subclass of ContentController (ties it to CMS, = ok...)
        $ctr = Controller::curr();
        if (is_subclass_of($ctr, "ContentController")) {
            if ($this->owner->getScheduledStatus() || $this->owner->getExpiredStatus()) {
                
                // if $this->owner is the actual page being visited (Director::get_current_page());
                $curpage = Director::get_current_page();
                if ($curpage->ID == $this->owner->ID) {
                    // we have to prevent visitors from actually visiting this page by redirecting to a 404
                    // This is a bit of a hack (redirect), but else visitors will be presented with a 
                    // 'login' screen in order to acquire sufficient privileges to view the page)
                    $errorPage = ErrorPage::get()->filter('ErrorCode', 404)->first();
                    if ($errorPage) {
                        $ctr->redirect($errorPage->Link(), 404);
                    } else {
                        // fallback (hack): redirect to anywhere, with a 404
                        $ctr->redirect(rtrim($this->owner->Link(), '/')."-404", 404);
                        //$ctr->redirect(Page::get()->first()->Link(), 404);
                    }
                }
                
                return false;
            } else {
                return true;
            }
        }
        
        // else, allow
        return true;
    }
    
    // workaround to add extra filtering to Object::get()'s
    public static function extraWhereQuery($extendedClass)
    {
        return "( \"{$extendedClass}\".\"Embargo\" IS NULL OR \"{$extendedClass}\".\"Embargo\" <= NOW() )
			AND
			( \"{$extendedClass}\".\"Expiry\" IS NULL OR \"{$extendedClass}\".\"Expiry\" >= NOW() )";
    }
    
    /*
     * Prevents page from begin included on Holder pages if under embargo or expired
     */
    public function augmentSQL(SQLQuery &$query)
    {
        
//		$myclass = $this->owner->className;
//		if (is_subclass_of(Controller::curr(), 'ContentController')) { // on frontend 
//			$query
//				->addWhere(array(
////					"\"{$myclass}\".\"Embargo\" IS NULL OR " .
//						"\"{$myclass}\".\"Embargo\" <= '" 
//						. SS_Datetime::now()->getValue() . "'",
////					"\"{$myclass}\".\"Embargo\" IS NULL OR \"{$myclass}\".\"Embargo\" < NOW()",
////					"\"{$myclass}\".\"Expiry\" IS NULL OR \"{$myclass}\".\"Expiry\" > NOW()",
//				));
//		}

//		$stage = Versioned::current_stage();
//		if($stage == 'Live' || !Permission::check("VIEW_DRAFT_CONTENT")) {
//			$query->addWhere("PublishDate < '" . Convert::raw2sql(SS_Datetime::now()) . "'");
//		}
    }
    
    /**
     * Maybe implement this as well?
     * This is a fix so that when we try to fetch subclasses of BlogPost,
     * lazy loading includes the BlogPost table in its query. Leaving this table out
     * means the default sort order column PublishDate causes an error.
     *
     * @see https://github.com/silverstripe/silverstripe-framework/issues/1682
    **/
//	public function augmentLoadLazyFields(SQLQuery &$query, &$dataQuery, $parent) {
//		// Ensures that we're joining the BlogPost table which holds required db fields.
//		$dataQuery->innerJoin("BlogPost", "`SiteTree`.`ID` = `BlogPost`.`ID`");
//	}
}
