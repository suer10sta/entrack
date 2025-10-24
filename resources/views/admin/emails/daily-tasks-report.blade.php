<!DOCTYPE html>
<html>
<head>
    <title>Daily Reports</title>
</head>
<body>
    <h2>Daily Reports</h2>
    <p>Hello,</p>
    <p>Please find attached the daily reports for {{ $enterprise->name }} ({{ \Carbon\Carbon::now()->format('d-m-Y') }}).</p>
    <p>The reports include:</p>
    <ul>
        <li>Tasks Report</li>
        <li>Projects Report</li>
        <li>Employees Report</li>
        <li>Clients Report</li>
        <li>Pointage Report</li>
        <li>Devis Report</li>
        <li>Invoices Report</li>
       
    </ul>
    <p>Best regards,<br>Your Application</p>
</body>
</html>