function loadZone(url, btn) {
    if(btn) {
        document.querySelectorAll('.sidebar-right button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    console.log("Attempting to load:", url); // Debug: See what URL is being requested

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error("HTTP " + response.status + " - File not found: " + url);
            }
            return response.text();
        })
        .then(html => {
            // Check if we got redirected to the login page by mistake
            if(html.includes('<title>Login</title>') || html.includes('name="accountid"')) {
                window.location.href = '../account/login.php'; // Force real redirect
                return;
            }
            document.getElementById('main-content').innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            document.getElementById('main-content').innerHTML = 
                "<div style='color:red; padding:20px; border:1px solid red; background:#fff;'>" +
                "<h3>Error Loading Content</h3>" +
                "<p>" + err.message + "</p>" +
                "<p>Check your console (F12) and Network tab for details.</p>" +
                "</div>";
        });
}

function submitForm(formElement, url) {
    let formData = new FormData(formElement);
    let btn = formElement.querySelector('button[type="submit"]');
    if(btn && btn.name) {
        formData.append(btn.name, btn.value);
    }

    document.getElementById('main-content').innerHTML = "<div style='text-align:center; padding:50px;'>Processing...</div>";

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("HTTP " + response.status + " - Error saving data.");
        }
        return response.text();
    })
    .then(responseHTML => {
        document.getElementById('main-content').innerHTML = responseHTML;
        let scripts = document.getElementById('main-content').querySelectorAll("script");
        scripts.forEach(script => eval(script.innerHTML));
    })
    .catch(err => {
        console.error(err);
        document.getElementById('main-content').innerHTML = 
            "<div style='color:red;'>System Error: " + err.message + "</div>";
    });
}

window.onload = function() {
    loadZone('welcome.php', null);
};