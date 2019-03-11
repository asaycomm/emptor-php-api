<?php
require_once __DIR__ . '/Config.php';

# Havuzdaki Kayıtlar
//$data = $emptor->getPoolItems(1,10);

# Üzerimdeki Kayıtlar
//$data = $emptor->getSelfItems(1,10);

# Kayıt Detayı
$data = $emptor->getRecord("e9f6f45a-50b8-41c2-8cbb-5e271f653d9a");


dd([
    'flow_data' =>  $emptor->mapRecordData("flow",$data["form"]),
    'customer_data' =>  $emptor->mapRecordData("customer",$data["form"]),
    'location_data' =>  $emptor->mapRecordData("location",$data["form"]),
    'devre_data' =>  $emptor->mapRecordData("data",$data["form"]),
]);

# Akış Eki Getir
//$data = $emptor->showAttachment("QX7qAE5KrfxGsmlyVydBwK7h9T29C9pMYHPy2TpGrDVkUc9qhC18t1oXGixDP+6n7IE98fGo0Oy38/I2+cYAITsqYLAuKY0QFaWJdk/RAzY6KWf8ZFlyDzFQ8SnaCfRiSIQTnQ9TidQMhqWhd9ccz7nU4Iu/YUkZqTTIuzD8EtA=","FW Akasya Brasserie Misafir internet hattı.msg");


?>