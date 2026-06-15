
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            600: '#7c3aed',
                            700: '#6d28d9',
                        },
                        dark: {
                            800: '#1e1e2d',
                            900: '#12141d',
                        },
                        slate: {
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
  const createResumeBtn = document.getElementById("createResumeBtn");

  createResumeBtn.addEventListener("click", function () {
    fetch("session_check.php")
      .then(response => response.text())
      .then(isLoggedIn => {
        if (isLoggedIn === "true") {
          window.location.href = "create_resume.html"; // or PHP
        } else {
          alert("You must log in or sign up first.");
          window.location.href = "login.html";
        }
      });
  });
});

    

    