<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>API Documentation - Your Service</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
 body {
  background-color: #f8f9fa;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.sidebar {
  width: 250px;
  position: fixed;
  height: 100%;
  background-color: #fff;
  border-right: 1px solid #ddd;
  padding: 2rem 1rem;
  overflow-y: auto;
  transition: width 0.3s ease, padding 0.3s ease;
  z-index: 999;
}

.sidebar.collapsed {
  width: 70px;
  padding: 2rem 0.5rem;
}

.sidebar h5 {
  margin-bottom: 1.5rem;
}

.sidebar.collapsed h5 {
  display: none;
}

.sidebar a {
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  color: #333;
  text-decoration: none;
  border-radius: 0.25rem;
  margin-bottom: 0.5rem;
  transition: background-color 0.2s;
}

.sidebar a i {
  min-width: 20px;
  text-align: center;
}

.sidebar a span {
  margin-left: 10px;
  white-space: nowrap;
}

.sidebar.collapsed a span {
  display: none;
}

.sidebar a:hover,
.sidebar a.active {
  background-color: #e2e6ea;
  font-weight: bold;
}

.main {
  margin-left: 270px;
  padding: 2rem;
  transition: margin-left 0.3s ease;
}

.main.collapsed {
  margin-left: 80px;
}

pre {
  background-color: #1e1e1e;
  color: #f1f1f1;
  padding: 1rem;
  border-radius: 8px;
  position: relative;
  overflow-x: auto;
}

pre::before {
  content: attr(data-lang);
  position: absolute;
  top: 0;
  right: 0;
  background: #343a40;
  color: white;
  font-size: 0.75rem;
  padding: 2px 8px;
  border-bottom-left-radius: 6px;
}

.copy-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background-color: #6c757d;
  color: #fff;
  border: none;
  padding: 5px 10px;
  border-radius: 5px;
  font-size: 0.8rem;
  cursor: pointer;
}

h2 {
  margin-top: 2rem;
}

.table-dark th {
  background-color: #343a40;
  color: white;
}


  </style>
</head>
<body>
    <script>
  document.getElementById('toggleSidebar').addEventListener('click', function () {
    document.querySelector('.sidebar').classList.toggle('collapsed');
    document.querySelector('.main').classList.toggle('collapsed');
  });
</script>

    
  <div class="sidebar">
    <a href="settings.php" class="btn btn-primary text-white"><i class="fa fa-arrow-left"></i> Back</a>
    <button id="toggleSidebar" class="btn btn-outline-primary mb-4 w-100">
  <i class="fas fa-bars"></i>
</button>
    <h5 class="mt-4">API Docs</h5>
<a href="#getting-started" class="active"><i class="fa fa-rocket"></i> <span>Getting Started</span></a>
<a href="#authentication"><i class="fa fa-key"></i> <span>Authentication</span></a>
<a href="#sample-request"><i class="fa fa-paper-plane"></i> <span>Sample Request</span></a>
<a href="#sample-response"><i class="fa fa-code"></i> <span>Sample Response</span></a>
<a href="#check-status"><i class="fa fa-search"></i> <span>Check Order Status</span></a>

  </div>

  <div class="main">
    <h3>POWERMYNET API Documentation</h3>
    <hr>
    <section id="getting-started">
      <h2>Getting Started</h2>
      <p>Welcome to the API documentation for your service. Use this API to initiate data bundle purchases and check transaction status.</p>
    </section>

    <section id="authentication">
      <h2>Authentication</h2>
      <p>Pass your generated <code>agent_api</code> key in the JSON body for all requests.</p>
    </section>


    <section id="sample-request">
      <h2>Sample Request (MTN,AT ISHARE,TELECEL)</h2>
      
      <h2>Endpoint</h2>
      <pre data-lang="URL"><code id="endpoint">https://api.codecraftnetwork.com/api/initiate.php</code><button class="copy-btn" onclick="copyCode('endpoint')">Copy</button></pre>
      
      <pre data-lang="JSON"><code id="requestCode">{
  "agent_api": "Api Key",
  "recipient_number": "0555555555",
  "network": "MTN, TELECEL, AT",
  "gig": "1",
  "reference_id": "e.g. ABEDC-BBADE-ABBCDD-AABCE-22334",
  "client_email": "Your Agents email address" // Will be used to check transaction history of your agents orders (All orders not single orders)
       }</code><button class="copy-btn" onclick="copyCode('requestCode')">Copy</button></pre>
    </section>
    
    <section id="sample-request">
      <h2>Sample Request (MTN Big Time & AT Big Time)</h2>
      
      <h2>Endpoint</h2>
      <pre data-lang="URL"><code id="endpoint">https://api.codecraftnetwork.com/api/special.php</code><button class="copy-btn" onclick="copyCode('endpoint')">Copy</button></pre>
      
      <pre data-lang="JSON"><code id="requestCode">{
  "agent_api": "Api Key",
  "recipient_number": "0554226398",
  "gig": "1",
  "network": "network", e.g. MTN, AT
  "reference_id": "e.g. ABEDC-BBADE-ABBCDD-AABCE-22334",
  "client_email": "Your Agents email address" // Will be used to check transaction history of your agents orders (All orders not single orders)
}</code><button class="copy-btn" onclick="copyCode('requestCode')">Copy</button></pre>
    </section>

    <section id="sample-response">
      <h2>Sample Response</h2>
      <pre data-lang="PHP"><code id="immediateresponse">
{
  "status": 200,
  "code": "200",
  "message": "Successful"
}
]);</code><button class="copy-btn" onclick="copyCode('immediateresponse')">Copy</button></pre>

      <h2>Check Order Status - MTN,Telecel,AT</h2>
      <pre data-lang="JSON"><code id="responseCode">{
      // Set the response content type to JSON
      header('Content-Type: application/json');
      url = https://api.codecraftnetwork.com/api/response_agent.php?agent_email=agent@example.com
      Data{
        reference_id
        agent_email
        network
        beneficiary
        gig
        order_date
        order_time 
        order_status 
      }
}</code><button class="copy-btn" onclick="copyCode('responseCode')">Copy</button></pre>
    </section>

    <section id="status-codes">
      <h2>Status Codes</h2>
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr><th>S/N</th><th>Code</th><th>Message</th></tr>
        </thead>
        <tbody>
          <tr><td>1</td><td>100</td><td>Admin is having low wallet balance</td></tr>
          <tr><td>2</td><td>101</td><td>Account is out of stock</td></tr>
          <tr><td>3</td><td>200</td><td>Successful</td></tr>
          <tr><td>4</td><td>500</td><td>Different Error messages</td></tr>
          <tr><td>5</td><td>102</td><td>Agent not found</td></tr>
          <tr><td>6</td><td>103</td><td>Price not found</td></tr>
          <tr><td>7</td><td>555</td><td>Network not found</td></tr>
        </tbody>
      </table>
    </section>

    <section id="sample-status">
      <h2>Sample Order Status Response</h2>
      <pre data-lang="JSON"><code id="responseCode">{
  "status": "Crediting successful",
  "message": "Data successfully delivered",
  "reference_id": "ABEDC-BBADE-ABBCDD-AABCE-22334",
  "order_date": "2025-06-20",
  "order_time": "14:32"
}</code><button class="copy-btn" onclick="copyCode('responseCode')">Copy</button></pre>
    </section>

    <section id="check-status">
      <h2>Check Order Status - MTN,Telecel,AT (Agent)</h2>
      <pre data-lang="JSON"><code id="responseCode">{
      url = https://api.codecraftnetwork.com/api/response_agent.php?client_email=agent@example.com
      Data{
        reference_id
        client_email
        network
        beneficiary
        gig
        price
        order_date
        order_time 
        order_status 
      }
}</code><button class="copy-btn" onclick="copyCode('responseCode')">Copy</button></pre>
    </section>
    
        <section id="check-status">
      <h2>Check Order Status - MTN,Telecel,AT (Customer)</h2>
      <pre data-lang="JSON"><code id="responseCode">{
      url = https://api.codecraftnetwork.com/api/response.php
      Data{

      }
}</code><button class="copy-btn" onclick="copyCode('responseCode')">Copy</button></pre>
    </section>

  </div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('toggleSidebar');
    if (toggleButton) {
      toggleButton.addEventListener('click', function () {
        document.querySelector('.sidebar').classList.toggle('collapsed');
        document.querySelector('.main').classList.toggle('collapsed');
      });
    }
  });
</script>

</body>
</html>
