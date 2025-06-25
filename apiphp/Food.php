<?php
// index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ค้นหาเมนูอาหาร - Spoonacular API</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .card img {
      object-fit: cover;
      height: 200px;
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <h1 class="mb-4 text-center">🍽️ ค้นหาเมนูอาหาร</h1>

    <div class="input-group mb-4">
      <input type="text" id="query" class="form-control" placeholder="เช่น pizza, chicken, salad">
      <button class="btn btn-primary" onclick="searchRecipes()">ค้นหา</button>
    </div>

    <h4 class="mb-3">🍱 เมนูอาหารแนะนำ</h4>
    <div id="results" class="row g-4"></div>
  </div>

  <!-- Bootstrap Modal -->
  <div class="modal fade" id="recipeModal" tabindex="-1" aria-labelledby="recipeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="recipeModalLabel">รายละเอียดเมนู</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
        </div>
        <div class="modal-body" id="modal-body-content">
          <!-- Recipe details will go here -->
        </div>
      </div>
    </div>
  </div>

  <script>
    const apiKey = 'dadbb894792d4ad489f29d0d150c29ac';

    window.addEventListener('DOMContentLoaded', fetchRecommendedRecipes);

    async function fetchRecommendedRecipes() {
      const resultsDiv = document.getElementById('results');
      resultsDiv.innerHTML = '<p class="text-center">⏳ กำลังโหลดเมนูแนะนำ...</p>';
      try {
        const response = await fetch(`https://api.spoonacular.com/recipes/random?number=8&apiKey=${apiKey}`);
        const data = await response.json();
        displayRecipes(data.recipes);
      } catch (error) {
        resultsDiv.innerHTML = `<p class="text-danger text-center">❌ ไม่สามารถโหลดเมนูแนะนำได้: ${error.message}</p>`;
      }
    }

    async function searchRecipes() {
      const query = document.getElementById('query').value.trim();
      const resultsDiv = document.getElementById('results');
      resultsDiv.innerHTML = '<p class="text-center">⏳ กำลังค้นหา...</p>';

      if (!query) {
        resultsDiv.innerHTML = '<p class="text-danger text-center">⚠️ กรุณากรอกชื่ออาหาร</p>';
        return;
      }

      try {
        const response = await fetch(`https://api.spoonacular.com/recipes/complexSearch?apiKey=${apiKey}&query=${encodeURIComponent(query)}`);
        const data = await response.json();
        if (!data.results || data.results.length === 0) {
          resultsDiv.innerHTML = '<p class="text-center">❌ ไม่พบเมนูที่ค้นหา</p>';
          return;
        }
        displayRecipes(data.results);
      } catch (error) {
        resultsDiv.innerHTML = `<p class="text-danger text-center">เกิดข้อผิดพลาด: ${error.message}</p>`;
      }
    }

    function displayRecipes(recipes) {
      const resultsDiv = document.getElementById('results');
      resultsDiv.innerHTML = '';
      recipes.forEach(recipe => {
        const col = document.createElement('div');
        col.className = 'col-md-3';
        col.innerHTML = `
          <div class="card h-100 shadow-sm">
            <img src="${recipe.image}" class="card-img-top" alt="${recipe.title}">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">${recipe.title}</h5>
              <button class="btn btn-outline-primary mt-auto" onclick="showRecipeDetails(${recipe.id})">ดูรายละเอียด</button>
            </div>
          </div>
        `;
        resultsDiv.appendChild(col);
      });
    }

    async function showRecipeDetails(recipeId) {
      const modalBody = document.getElementById('modal-body-content');
      modalBody.innerHTML = `<p class="text-center">⏳ กำลังโหลด...</p>`;
      const modal = new bootstrap.Modal(document.getElementById('recipeModal'));

      try {
        const response = await fetch(`https://api.spoonacular.com/recipes/${recipeId}/information?apiKey=${apiKey}`);
        const data = await response.json();

        modalBody.innerHTML = `
          <h2>${data.title}</h2>
          <img src="${data.image}" class="img-fluid my-3 rounded">
          <p><strong>🍳 วัตถุดิบ:</strong> ${data.extendedIngredients.map(i => i.original).join(', ')}</p>
          <p><strong>📖 วิธีทำ:</strong><br>${data.instructions || 'ไม่มีข้อมูลวิธีทำ'}</p>
        `;
        modal.show();
      } catch (error) {
        modalBody.innerHTML = `<p class="text-danger text-center">ไม่สามารถโหลดข้อมูลได้: ${error.message}</p>`;
        modal.show();
      }
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
