<h1>Alert Information</h1>

<table width="100%" border="1">
    <tr>
        <th>ID</th>
        <th>Date</th>
        <th>EventID</th>
        <th>Magnitude</th>
        <th>Severity</th>
    </tr>
    <tr>
        <td><?= $alert->id ?></td>
        <td><?= date('M d, Y H:i',$alert->date) ?></td>
        <td><?= \app\models\WeatherAlert::getAlertTypeByEventId($alert->event) ?></td>
        <td><?= $alert->magnitude ?></td>
        <td><?= $alert->severity ?></td>
    </tr>
</table>

<h1>Affected Properties Information</h1>

<table width="100%" border="1">
    <tr>
        <th>ID</th>
        <th>Street Address</th>
        <th>City</th>
        <th>State</th>
        <th>Zip Code</th>
        <th>Client</th>
        <th>Latitude</th>
        <th>Longitude</th>
    </tr>
    <?php foreach ($affected as $property) { ?>
        <tr>
            <td><?= $property->id ?></td>
            <td><?= $property->streetAddress ?></td>
            <td><?= $property->city ?></td>
            <td><?= $property->state ?></td>
            <td><?= $property->zipcode ?></td>
            <td><?= $property->client ?></td>
            <td><?= $property->latitude ?></td>
            <td><?= $property->longitude ?></td>
        </tr>
    <?php } ?>


</table>