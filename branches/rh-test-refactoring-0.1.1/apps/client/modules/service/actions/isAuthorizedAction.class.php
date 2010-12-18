<?php
#
# Check if a user is authorized - for testing and bootstraps
#
class isAuthorizedAction extends sfAction
{
  public function execute($request)
  {
    echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
    echo "<root>\r\n";
    echo "  <authorized>1</authorized>\r\n";
    echo "</root>\r\n";
    exit;
  }
}