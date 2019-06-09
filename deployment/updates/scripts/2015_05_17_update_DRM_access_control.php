<?php
ini_set("memory_limit","1024M");

chdir(__DIR__);
require_once (__DIR__ . '/../../bootstrap.php');

class vAccessControlPlayReadyPolicyAction extends vRuleAction
{
    protected $policyId;

    public function __construct()
    {
        parent::__construct(DrmAccessControlActionType::DRM_POLICY);
    }
    public function getPolicyId()
    {
        return $this->policyId;
    }
    public function setPolicyId($policyId)
    {
        $this->policyId = $policyId;
    }
}

$permCrit = new Criteria();
$permCrit->add(PermissionPeer::NAME, 'PLAYREADY_PLUGIN_PERMISSION', Criteria::EQUAL);
$permCrit->addAnd(PermissionPeer::STATUS, 1, Criteria::EQUAL);
$permissions = PermissionPeer::doSelect($permCrit);
$partners = array();
foreach ($permissions as $perm)
{
    $partners[] = $perm->getPartnerId();
}

VidiunLog::debug("Partners are '".print_r($partners,true)."'");

$c = new Criteria();
$c->add(accessControlPeer::PARTNER_ID, $partners, Criteria::IN);
$c->addAnd(accessControlPeer::RULES, '%vAccessControlPlayReadyPolicyAction%', Criteria::LIKE);
$acs = accessControlPeer::doSelect($c);
foreach ($acs as $ac)
{
    VidiunLog::debug("checking access control '".$ac->getId()."'");
    $rules = $ac->getRulesArray();
    foreach ($rules as $rule)
    {
        $actions = $rule->getActions();
        $j = 0;
        foreach ($actions as $action)
        {
            VidiunLog::debug("checking action '".print_r($action,true)."'");
            if (get_class($action) == 'vAccessControlPlayReadyPolicyAction')
            {
                VidiunLog::debug("replacing vAccessControlPlayReadyPolicyAction with vAccessControlDrmPolicyAction");
                $newAction = new vAccessControlDrmPolicyAction();
                $newAction->setPolicyId($action->getPolicyId());
                $actions[$j] = $newAction;
                $rule->setActions($actions);
                $ac->setRulesArray($rules);
                $ac->save();
                VidiunLog::debug("finished saving");
            }
            $j++;
        }
    }
}

