<!DOCTYPE html>
<html lang="fr-FR">

<head>
    <meta charset="UTF-8">
    <title>Purchase Courses</title>
    <link style="text/css" rel="stylesheet" href="css/global.css">
    <script src="./js/utils.js"></script>
    <style>
        .purchase-container {
            position: relative;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .course-list {
            margin: 2rem 0;
        }

        .course-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin: 0.5rem 0;
            background: var(--background-light);
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .course-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 6px rgba(52, 152, 219, 0.1);
        }

        input[type="checkbox"] {
            width: 1.2rem;
            height: 1.2rem;
            margin-right: 1rem;
        }

        .purchase-btn,
        .refund-btn {
            width: 100%;
            padding: 1rem 2rem;
            margin-top: 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.3rem;
            transition: background 0.3s ease;
            color: white;
        }

        .purchase-btn {
            background: var(--primary-color);
        }

        .purchase-btn:hover {
            background: var(--primary-dark);
        }

        .refund-btn {
            background: #e74c3c;
        }

        .refund-btn:hover {
            background: #c0392b;
        }

        .validation-message {
            color: #ff4444;
            text-align: center;
            padding: 1rem;
            margin: 1rem 0;
        }

        #username {
            text-align: center;
            font-size: 2rem;
        }

        label {
            position: relative;
        }

        label>span {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            margin-right: 30px;
        }
    </style>
</head>

<body>
    <div class="purchase-container">
        <div class="close-btn" onclick="window.location.href='/'">×</div>
        <h1 id="username">Select Courses to Purchase</h1>
        <div class="course-list" id="courseList">
            <div class="loading">Loading courses...</div>
        </div>
        <div class="validation-message hidden" id="errorMessage"></div>
        <button class="refund-btn" id="refundBtn" onclick="handleRefund()">Request Refund</button>
        <button class="purchase-btn" onclick="handlePurchase()">Purchase Selected Courses</button>
    </div>
    <script src="js/utils.js"></script>
    <script>

        async function renderCourses(data) {
            try {
                const container = document.getElementById('courseList');
                container.innerHTML = data.map(course => `
                    <label class="course-item">
                        <input type="checkbox" name="course_id" value="${course.course_id}">
                        Assimil French Chapter ${course.course_id}
                        ${course.status === 1
                        ? '<span class="free-tag">FREE</span>'
                        : course.status === 2
                            ? '<span class="purchase-tag">PURCHASED</span>'
                            : ''
                    }
                    </label>
                `).join('');
            } catch (error) {
                showError('Failed to load courses');
            }
        }


        async function handlePurchase() {
            const checkboxes = document.querySelectorAll('input[name="course_id"]:checked');
            const courseIds = Array.from(checkboxes).map(cb => cb.value);

            if (courseIds.length === 0) {
                return showError('Please select at least one course');
            }

            try {
                const response = await fetch('/api/purchase/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('token')}`
                    },
                    body: JSON.stringify({ course_ids: courseIds })
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Purchase successful!');
                    window.location.href = '/';
                } else {
                    showError(result.msg || 'Purchase failed');
                }
            } catch (error) {
                showError('Network error');
            }
        }

        async function handleRefund() {
            const checkboxes = document.querySelectorAll('input[name="course_id"]:checked');
            const courseIds = Array.from(checkboxes).map(cb => cb.value);

            if (courseIds.length === 0) {
                return showError('Please select at least one course for refund');
            }

            try {
                const response = await fetch('/api/purchase/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('token')}`
                    },
                    body: JSON.stringify({ course_ids: courseIds })
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Refund request submitted successfully!');
                    window.location.reload();
                } else {
                    showError(result.msg || 'Refund request failed');
                }
            } catch (error) {
                showError('Network error while processing refund');
            }
        }

        window.addEventListener('DOMContentLoaded', () => { loadCourses().then(data => renderCourses(data)); });
        document.getElementById('username').textContent = "Hi " + localStorage.getItem('username') + ", please select courses to purchase";
    </script>
</body>

</html>