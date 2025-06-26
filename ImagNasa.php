<?php
    // index.php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>NASA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
    .card img {
        object-fit: cover;
        height: 200px;
    }
    </style>
</head>

<body>
    <div class="container py-5">
        <h1 class="mb-4 text-center">ค้นหาภาพถ่ายตามวัน-เดือน-ปี</h1>

        <!-- ช่องกรอกข้อความและปุ่มค้นหา -->
        <div class="input-group mb-4">
            <input type="text" id="query" class="form-control" placeholder="กรุณากรอกวันที่ (YYYY-MM-DD)" />
            <button class="btn btn-primary" onclick="searchRecipes()">ค้นหา</button>
        </div>

        <!-- ปุ่มกลับหน้าหลัก -->
        <div class="d-flex justify-content-end mb-3">
            <button id="backButton" class="btn btn-secondary d-none"
                onclick="fetchRecommendedRecipes()">กลับหน้าหลัก</button>
        </div>

        <!-- ส่วนแสดงภาพ -->
        <h4 class="mb-3">ภาพแนะนำ</h4>
        <div id="results" class="row g-4"></div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="recipeModal" tabindex="-1" aria-labelledby="recipeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="recipeModalLabel">รายละเอียดภาพ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                </div>
                <div class="modal-body" id="modal-body-content"></div>
            </div>
        </div>
    </div>

    <script>
    const apiKey = 'E5BLnhhuj9K0iqmbyeQFkatXp74UiK0HNNyBLIQ2';
    window.recipes = [];

    async function fetchRecommendedRecipes() {
        const resultsDiv = document.getElementById('results');
        const backButton = document.getElementById('backButton');

        backButton.classList.add('d-none');
        document.getElementById('query').value = '';
        resultsDiv.innerHTML = '<p class="text-center">กำลังโหลดเมนูแนะนำ...</p>';

        // ✅ ถ้ามีข้อมูลแล้ว ใช้ข้อมูลเดิม
        if (window.recipes.length > 0) {
            displayRecipes(window.recipes);
            return;
        }

        try {
            const recipes = [];
            const today = new Date();
            for (let i = 0; i < 100; i++) {
                const date = new Date(today);
                date.setDate(today.getDate() - i);
                const dateString = date.toISOString().split('T')[0];

                const response = await fetch(
                    `https://api.nasa.gov/planetary/apod?api_key=${apiKey}&date=${dateString}`);
                if (!response.ok) continue;
                const data = await response.json();

                if (data.media_type === 'image') {
                    recipes.push({
                        id: recipes.length,
                        title: data.title,
                        image: data.url,
                        explanation: data.explanation,
                        date: data.date,
                    });
                }
            }

            window.recipes = recipes;
            displayRecipes(recipes);
        } catch (error) {
            resultsDiv.innerHTML = `<p class="text-danger text-center">เกิดข้อผิดพลาด: ${error.message}</p>`;
        }
    }

    function displayRecipes(recipes) {
        const resultsDiv = document.getElementById('results');
        resultsDiv.innerHTML = '';

        recipes.forEach((recipe) => {
            const col = document.createElement('div');
            col.className = 'col-md-3';
            col.innerHTML = `
                <div class="card h-100 shadow-sm">
                    <img src="${recipe.image}" class="card-img-top" alt="${recipe.title}" />
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">${recipe.title}</h5>
                        <p class="card-text text-muted mb-2">${recipe.date}</p>
                        <button class="btn btn-outline-primary mt-auto" onclick="showRecipeDetails(${recipe.id})">
                            ดูรายละเอียด
                        </button>
                    </div>
                </div>
            `;
            resultsDiv.appendChild(col);
        });
    }

    function showRecipeDetails(id) {
        const modalBody = document.getElementById('modal-body-content');
        const recipe = window.recipes.find(r => r.id === id);

        if (!recipe) {
            modalBody.innerHTML = `<p class="text-danger text-center">ไม่พบข้อมูลรายละเอียด</p>`;
        } else {
            modalBody.innerHTML = `
                <h2>${recipe.title}</h2>
                <img src="${recipe.image}" class="img-fluid my-3 rounded" />
                <p><strong>รายละเอียด:</strong><br>${recipe.explanation}</p>
            `;
        }

        const modal = new bootstrap.Modal(document.getElementById('recipeModal'));
        modal.show();
    }

    async function searchRecipes() {
        const resultsDiv = document.getElementById('results');
        const query = document.getElementById('query').value.trim();
        const backButton = document.getElementById('backButton');

        const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
        if (!dateRegex.test(query)) {
            resultsDiv.innerHTML =
                '<p class="text-danger text-center">กรุณากรอกวันที่ในรูปแบบ YYYY-MM-DD เท่านั้น</p>';
            backButton.classList.add('d-none');
            return;
        }

        resultsDiv.innerHTML = '<p class="text-center">กำลังค้นหาภาพ...</p>';

        // ✅ ค้นหาในข้อมูลเดิมก่อน
        const found = window.recipes.find(r => r.date === query);
        if (found) {
            displayRecipes([found]);
            backButton.classList.remove('d-none');
            return;
        }

        try {
            const response = await fetch(`https://api.nasa.gov/planetary/apod?api_key=${apiKey}&date=${query}`);
            if (!response.ok) throw new Error(`ไม่พบภาพในวันที่ ${query}`);
            const data = await response.json();

            if (data.media_type !== 'image') {
                resultsDiv.innerHTML = `<p class="text-center">วันที่ ${query} ไม่มีภาพ (อาจเป็นวิดีโอ)</p>`;
                backButton.classList.remove('d-none');
                return;
            }

            const recipe = {
                id: window.recipes.length,
                title: data.title,
                image: data.url,
                explanation: data.explanation,
                date: data.date,
            };

            // ✅ เพิ่มเข้า array
            window.recipes.push(recipe);
            displayRecipes([recipe]);
            backButton.classList.remove('d-none');
        } catch (error) {
            resultsDiv.innerHTML = `<p class="text-danger text-center">${error.message}</p>`;
            backButton.classList.remove('d-none');
        }
    }

    window.addEventListener('DOMContentLoaded', fetchRecommendedRecipes);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>