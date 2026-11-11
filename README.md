Northbeam Academy: online application form
A web application built with HTML, CSS, JavaScript, jQuery and PHP for an online application/registration form. The form takes the applicant's details and, when they press the submit button, shows them back in the browser as a formatted, styled (and printable) page.

Northbeam Academy is a made-up school, used only for this assignment.

What it does
Requirement	Where
HTML form	index.php: three steps (About you, Programme, Statement) with text, email, tel, date, select, radio, checkbox and textarea fields
CSS styling	assets/css/style.css (form), assets/css/result.css (result page and print layout)
JavaScript + jQuery	assets/js/app.js: step navigation, validation, error messages, character counter, submit
Animation	GSAP 3 (from a CDN): page entrance, step slides, progress bar, error shake, tick drawing on the result page
PHP	submit.php checks every field again on the server; result.php prints the submitted data
Display on success	result.php: reference number, every answer in sections, Print button, copy reference
How a submission flows
index.php shows the form. jQuery checks each step before moving on.
The form POSTs to submit.php, which cleans and validates every field (it never trusts the browser), and checks a CSRF token.
If something is wrong, it sends the visitor back to the form with their answers kept and each error under its field.
If everything is right, it gives the application a reference (NB-27-XXXXXX), saves it in the session (and to data/applications.jsonl when the host allows writing), and redirects to result.php. This Post/Redirect/Get pattern means refreshing the result page doesn't submit the form again.
result.php shows the application on a styled page. Every value is escaped with htmlspecialchars before it's printed.
Files
registration-app/
├── index.php            the form
├── submit.php           server-side validation, then redirect
├── result.php           the formatted result page
├── includes/config.php  option lists, helpers, session
├── assets/
│   ├── css/style.css    form styles
│   ├── css/result.css   result and print styles
│   ├── js/app.js        jQuery + GSAP for the form
│   ├── js/result.js     jQuery + GSAP for the result page
│   └── img/             logo mark and photograph
├── data/.htaccess       keeps saved applications private
└── Dockerfile           for cloud hosting
Run it on your computer
You need PHP 8.1 or newer.

php -S localhost:8000
Then open http://localhost:8000.

Host it on a cloud platform
Pick one of these options.

Option A: Render (free, uses the Dockerfile)
Push this folder to a GitHub repository.
On https://render.com choose New → Web Service and connect the repository.
Render finds the Dockerfile. Leave the defaults and press Create Web Service.
After the build, Render gives you a URL like https://northbeam-apply.onrender.com.
Railway, Google Cloud Run and Fly.io take the same Dockerfile; each one passes the port in $PORT, which the Dockerfile reads.

Option B: InfinityFree or any shared PHP host
Make a free account and a site on https://www.infinityfree.com.
Open the File Manager (or connect with FTP) and upload everything inside this folder into htdocs/.
Open your site's address.
Option C: Azure App Service (PHP)
az webapp up --runtime "PHP:8.3" --sku F1 --name northbeam-apply
Run it from inside this folder. Azure prints the site's URL when it finishes.

Settings
APP_TZ: time zone for the time shown on the result page, for example Asia/Karachi. It defaults to UTC.
Credits
Photograph: Jan Kahánek, CC0, via Wikimedia Commons. Fonts: Red Hat Display and Red Hat Text (Google Fonts). Libraries: jQuery 3.7.1, GSAP 3.12.5.
