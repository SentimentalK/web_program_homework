<!DOCTYPE html>
<html lang="fr-FR">

<head>
    <meta charset="UTF-8">
    <title>Assimil Course Home</title>
    <link style="text/css" rel="stylesheet" href="css/global.css">
    <script src="./js/utils.js"></script>
    <style>

        body {
            max-width: 800px;
        }


        #auth-bar {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 1000;
        }

        #auth-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        #auth-btn:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        h1 {
            margin: 3rem 0 0;
            letter-spacing: -0.5px;
        }

        #course-container {
            margin: 2rem 0;
        }

        .course-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .course-item {
            background: white;
            border-radius: 8px;
            margin: 1rem 0;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .course-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .course-link {
            text-decoration: none;
            color: #2c3e50;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .error {
            background: #fff5f5;
            border: 1px solid #ff4444;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: center;
        }

        .error button {
            margin-top: 1rem;
            padding: 0.5rem 1.5rem;
        }

        #search-input {
            width: 100%;
            padding: 0.8rem;
            margin: 1rem 0 2rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: all 0.3s ease;
            text-align: center;
        }

        #search-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
    </style>
</head>

<body>
    <div id="auth-bar">
        <button id="auth-btn">Login</button>
    </div>

    <h1>Assimil French Course</h1>

    <input type="text" id="search-input" placeholder="Search Courses (e.g. 'free', 'purchased', 'chapter 1')" />

    <div id="course-container">
        <div class="loading">Loading courses...</div>
    </div>

    <script>
        function checkLoginStatus() {
            const token = localStorage.getItem('token');
            const authBtn = document.getElementById('auth-btn');

            if (token) {
                authBtn.textContent = 'Logout';
                authBtn.onclick = () => {
                    localStorage.removeItem('token');
                    window.location.reload();
                };
            } else {
                authBtn.textContent = 'Login';
                authBtn.onclick = () => window.location.href = 'login.php';
            }
        }

        function filterCourses(searchTerm) {
            const courseItems = document.querySelectorAll('.course-item');
            courseItems.forEach(item => {
                const link = item.querySelector('.course-link');
                const textContent = link.textContent.toLowerCase();
                item.style.display = textContent.includes(searchTerm) ? '' : 'none';
            });
        }

        function renderCourses(courses) {
            const container = document.getElementById('course-container');
            container.innerHTML = '';

            const list = document.createElement('ul');
            list.className = 'course-list';

            courses.forEach(course => {
                const listItem = document.createElement('li');
                listItem.className = 'course-item';

                const link = document.createElement('a');
                link.className = 'course-link';
                link.href = `content.php?course_id=${course.course_id}`;

                
                const titleText = `Assimil French Chapter ${course.course_id}`;
                link.appendChild(document.createTextNode(titleText));

                if (course.status === 1) {
                    const freeTag = document.createElement('span');
                    freeTag.className = 'free-tag';
                    freeTag.textContent = 'FREE';
                    link.appendChild(freeTag);
                } else if (course.status === 2) {
                    const purchasedTag = document.createElement('span');
                    purchasedTag.className = 'purchase-tag';
                    purchasedTag.textContent = 'PURCHASED';
                    link.appendChild(purchasedTag);
                }

                listItem.appendChild(link);
                list.appendChild(listItem);
            });

            container.appendChild(list);
        }

        function showError(message) {
            const container = document.getElementById('course-container');
            container.innerHTML = `
                <div class="error">
                    <strong>Error:</strong> ${message}
                    <button onclick="location.reload()">Reload</button>
                </div>
            `;
        }

        window.addEventListener('DOMContentLoaded', () => {
            checkLoginStatus();
            
            document.getElementById('search-input').addEventListener('input', function(e) {
                const searchTerm = e.target.value.trim().toLowerCase();
                filterCourses(searchTerm);
            });

            loadCourses()
                .then(courses => renderCourses(courses))
                .catch(err => showError(err.message));
        });
    </script>
</body>

</html>