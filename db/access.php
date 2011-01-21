<?php

$coursereport_lessonpreg_capabilities = array(

    'coursereport/lessonpreg:view' => array(
        'riskbitmask' => RISK_DATALOSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'editingteacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'mod/lesson:edit',
    )
);

?>
