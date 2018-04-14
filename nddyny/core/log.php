<?php
function log_error($category, $Result)
{
    $Result = R::castResultObject($Result);
    print_r([$category, $Result]);
    syslog(LOG_ERR, "app,|`$category,|`{$Result->json()}");
}