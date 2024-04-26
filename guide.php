<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Step-by-Step Guide</title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f8f8f8;
    }

    header {
      background-color: dodgerblue;
      color: #fff;
      text-align: left;
      padding: 1em;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    h1 {
      margin: 0;
      animation: moveText 2s infinite;
    }

    @keyframes moveText {
      0%, 100% {
        transform: translateX(0);
      }
      50% {
        transform: translateX(10px);
      }
    }

    main {
      max-width: 800px;
      margin: 20px auto;
      padding: 20px;
      background-color: #fff;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    section {
      margin-bottom: 20px;
    }

    h2 {
      cursor: pointer;
      background-color: #4CAF50;
      color: #fff;
      padding: 10px;
      margin: 0;
    }

    .section-content {
      display: none;
      padding: 10px;
    }

    .dropdown,
    .separator {
      margin-top: 10px;
    }

    .dropdown-heading {
      cursor: pointer;
      background-color: #008CBA;
      color: #fff;
      padding: 10px;
      margin: 0;
    }

    .dropdown-content {
      display: none;
      padding: 10px;
      background-color: #eee;
    }

    .pdf-link {
      display: block;
      margin-bottom: 10px;
      color: #333;
      text-decoration: none;
      padding: 5px;
      border: 1px solid #ddd;
      background-color: #fff;
      transition: background-color 0.3s ease;
    }

    .pdf-link:hover {
      background-color: #f5f5f5;
    }

    .pdf-icon,
    .view-icon {
      margin-right: 5px;
    }
    
    h2::after
    {
      content: '\25B6'; /* Unicode character for a right-pointing triangle */
      float: right;
    }

    h2.expanded::after {
      transform: rotate(90deg);
    }

    img {
      height: 60px;
      width: 100px;
      margin-right: 1em;
    }

    .back-button {
      display: inline-block;
      margin-top: 1em;
      padding: 0.5em 1em;
      background-color: #333;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      transition: background-color 0.3s;
    }

    .back-button:hover {
      background-color: #4CAF50;
    }

    .back-icon {
      margin-right: 0.5em;
      font-size: 18px;
    }

  </style>
</head>

<body>
  <header>
    <a href="index.html" class="back-button">
      <span class="back-icon">⬅</span> Back to Homepage
    </a>
    <div class="first-section">
      <div class="header-item center">
        <img src="Logo_final.png" alt="Logo">
      </div>
    </div>
    <h1>Step-by-Step Guide</h1>
  </header>

  <main>
    <section id="eod-usage">
      <h2 onclick="toggleSection('eod-usage')">EOD Platform Usage</h2>
      <div id="content-eod-usage" class="section-content">
        <a href="pdfs/EOD Platform Usage.pdf" class="pdf-link" download>
          <span class="pdf-icon">📥</span> Download PDF
        </a>
        <a href="pdfs/EOD Platform Usage.pdf" class="pdf-link" target="_blank">
          <span class="view-icon">👁️</span> View PDF
        </a>
      </div>
    </section>

    <section id="end-users">
      <h2 onclick="toggleSection('end-users')">End-users</h2>
      <div id="content-end-users" class="section-content">
        <!-- Dropdowns go here -->
        <div class="dropdown">
          <h3 class="dropdown-heading" onclick="toggleDropdown('end-user-registration')">End-user Registration</h3>
          <div id="end-user-registration" class="dropdown-content">
            <h4>Contents</h4>
            <a href="pdfs/EOD_End-user_Registration_Guide.pdf" class="pdf-link" download>
              <span class="pdf-icon">📥</span> Download PDF
            </a>
            <a href="pdfs/EOD_End-user_Registration_Guide.pdf" class="pdf-link" target="_blank">
              <span class="view-icon">👁️</span> View PDF
            </a>
          </div>
        </div>
        <div class="separator"></div>

        <div class="dropdown">
          <h3 class="dropdown-heading" onclick="toggleDropdown('general-feedback')">General Feedback</h3>
          <div id="general-feedback" class="dropdown-content">
            <h4>Contents</h4>
            <a href="pdfs/EOD_Feedback_Guide_General.pdf" class="pdf-link" download>
              <span class="pdf-icon">📥</span> Download PDF
            </a>
            <a href="pdfs/EOD_Feedback_Guide_General.pdf" class="pdf-link" target="_blank">
              <span class="view-icon">👁️</span> View PDF
            </a>
          </div>
        </div>
        <div class="separator"></div>

        <div class="dropdown">
          <h3 class="dropdown-heading" onclick="toggleDropdown('specific-feedback')">Specific Feedback</h3>
          <div id="specific-feedback" class="dropdown-content">
            <h4>Contents</h4>
            <div class="dropdown">
              <h3 class="dropdown-heading" onclick="toggleDropdown('registered-end-user')">Registered End-user</h3>
              <div id="registered-end-user" class="dropdown-content">
                <h4>Contents</h4>
                <a href="pdfs/EOD_Registered_User_Feedback_Guide.pdf" class="pdf-link" download>
                  <span class="pdf-icon">📥</span> Download PDF
                </a>
                <a href="pdfs/EOD_Registered_User_Feedback_Guide.pdf" class="pdf-link" target="_blank">
                  <span class="view-icon">👁️</span> View PDF
                </a>
              </div>
            </div>
            <div class="separator"></div>

            <div class="dropdown">
              <h3 class="dropdown-heading" onclick="toggleDropdown('non-registered-user')">Non-registered User</h3>
              <div id="non-registered-user" class="dropdown-content">
                <h4>Contents</h4>
                <a href="pdfs/EOD_Non_Registered_User_Feedback_Guide.pdf" class="pdf-link" download>
                  <span class="pdf-icon">📥</span> Download PDF
                </a>
                <a href="pdfs/EOD_Non_Registered_User_Feedback_Guide.pdf" class="pdf-link" target="_blank">
                  <span class="view-icon">👁️</span> View PDF
                </a>
              </div>
            </div>
          </div>
        </div>
        <div class="separator"></div>

        <div class="dropdown">
          <h3 class="dropdown-heading" onclick="toggleDropdown('end-user-login-and-dashboard')">End-user Login and Dashboard</h3>
          <div id="end-user-login-and-dashboard" class="dropdown-content">
            <h4>Contents</h4>
            <a href="pdfs/EOD_Logged_In_User_Guide.pdf" class="pdf-link" download>
              <span class="pdf-icon">📥</span> Download PDF
            </a>
            <a href="pdfs/EOD_Logged_In_User_Guide.pdf" class="pdf-link" target="_blank">
              <span class="view-icon">👁️</span> View PDF
            </a>
          </div>
        </div>
        <div class="separator"></div>

        <div class="dropdown">
          <h3 class="dropdown-heading" onclick="toggleDropdown('browse-new-developments')">Browse New developments</h3>
          <div id="browse-new-developments" class="dropdown-content">
            <h4>Contents</h4>
            <a href="pdfs/EOD_Browsing_Developments_Guide.pdf" class="pdf-link" download>
              <span class="pdf-icon">📥</span> Download PDF
            </a>
            <a href="pdfs/EOD_Browsing_Developments_Guide.pdf" class="pdf-link" target="_blank">
              <span class="view-icon">👁️</span> View PDF
            </a>
          </div>
        </div>
      </div>
    </section>

    <section id="professionals">
      <h2 onclick="toggleSection('professionals')">Professionals</h2>
      <div id="content-professionals" class="section-content">
        <div class="dropdown">
          <h3 class="dropdown-heading" onclick="toggleDropdown('property-manager')">Property Manager</h3>
          <div id="property-manager" class="dropdown-content">
            <h4>Contents</h4>
            <a href="pdfs/Property_Manager_Guide.pdf" class="pdf-link" download>
              <span class="pdf-icon">📥</span> Download PDF
            </a>
            <a href="pdfs/Property_Manager_Guide.pdf" class="pdf-link" target="_blank">
              <span class="view-icon">👁️</span> View PDF
            </a>
          </div>
        </div>
        <div class="separator"></div>

        <div class="dropdown">
          <h3 class="dropdown-heading" onclick="toggleDropdown('architect')">Architect</h3>
          <div id="architect" class="dropdown-content">
            <h4>Contents</h4>
            <a href="pdfs/EOD_Platform_Guide_Architect.pdf" class="pdf-link" download>
              <span class="pdf-icon">📥</span> Download PDF
            </a>
            <a href="pdfs/EOD_Platform_Guide_Architect.pdf" class="pdf-link" target="_blank">
              <span class="view-icon">👁️</span> View PDF
            </a>
          </div>
        </div>
        <div class="separator"></div>

        <div class="dropdown">
          <h3 class="dropdown-heading" onclick="toggleDropdown('others')">Others</h3>
          <div id="others" class="dropdown-content">
            <h4>Contents</h4>
            <a href="pdfs/EOD_Platform_Guide_Others.pdf" class="pdf-link" download>
              <span class="pdf-icon">📥</span> Download PDF
            </a>
            <a href="pdfs/EOD_Platform_Guide_Others.pdf" class="pdf-link" target="_blank">
              <span class="view-icon">👁️</span> View PDF
            </a>
          </div>
        </div>
      </div>
    </section>

    <section id="building-developers">
      <h2 onclick="toggleSection('building-developers')">Building Developers</h2>
      <div id="content-building-developers" class="section-content">
        <a href="pdfs/EOD_Platform_Developer_Guide_With_Images_Integrated.pdf" class="pdf-link" download>
          <span class="pdf-icon">📥</span> Download PDF
        </a>
        <a href="pdfs/EOD_Platform_Developer_Guide_With_Images_Integrated.pdf" class="pdf-link" target="_blank">
          <span class="view-icon">👁️</span> View PDF
        </a>
      </div>
    </section>
  </main>

  <script>
    function toggleSection(sectionId) {
  var sectionContent = document.getElementById('content-' + sectionId);
  sectionContent.style.display = (sectionContent.style.display === 'block') ? 'none' : 'block';
  var sectionHeading = document.querySelector('#' + sectionId + ' h2');
  sectionHeading.classList.toggle('expanded');
}

function toggleDropdown(dropdownId) {
  var dropdownContent = document.getElementById(dropdownId);
  dropdownContent.style.display = (dropdownContent.style.display === 'block') ? 'none' : 'block';
  var dropdownHeading = document.querySelector('#' + dropdownId + ' h3');
  dropdownHeading.classList.toggle('expanded');
}
  </script>
</body>

</html>
