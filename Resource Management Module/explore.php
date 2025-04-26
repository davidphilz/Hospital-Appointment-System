<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Explore Our Story | Hospital System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
    }
  
    body {
      display: flex;
      flex-direction: column;
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      color: #333;
    }
    main {
      flex: 1;
    }
    h2 {
      font-weight: 600;
      margin-bottom: 1rem;
    }
    .hero-story {
      background: url('img/story_hero.jpg') center/cover no-repeat;
      position: relative;
      min-height: 60vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      overflow: hidden;
    }
    .hero-story::before {
      content: "";
      position: absolute;
      top: 0; right: 0; bottom: 0; left: 0;
      background: rgba(0,0,0,0.5);
      z-index: 1;
    }
    .hero-story-content {
      position: relative;
      z-index: 2;
      max-width: 800px;
      padding: 20px;
      color: #fff;
    }
    .hero-story-content h1 {
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    .hero-story-content p {
      font-size: 1.25rem;
      margin-bottom: 20px;
    }
    
    .timeline {
      position: relative;
      padding: 2rem 0;
      margin-top: 2rem;
    }
    .timeline::after {
      content: '';
      position: absolute;
      top: 0;
      bottom: 0;
      left: 50%;
      width: 4px;
      background: #007bff;
      transform: translateX(-50%);
    }
    .timeline-item {
      position: relative;
      width: 50%;
      padding: 1rem 2rem;
      box-sizing: border-box;
      clear: both;
    }
    .timeline-item.left {
      float: left;
      text-align: right;
    }
    .timeline-item.right {
      float: right;
      text-align: left;
    }
    .timeline-item::before {
      content: "";
      position: absolute;
      top: 1rem;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: #fff;
      border: 4px solid #007bff;
      z-index: 2;
    }
    .timeline-item.left::before {
      right: -10px;
    }
    .timeline-item.right::before {
      left: -10px;
    }
    @media (max-width: 768px) {
      .timeline::after {
        left: 8%;
      }
      .timeline-item {
        width: 100%;
        padding-left: 30px;
        padding-right: 25px;
        float: none;
        text-align: left;
      }
      .timeline-item::before {
        left: 0;
      }
    }
    .commitment-section {
      background: #fff;
      padding: 60px 0;
      text-align: center;
    }

    footer {
      background-color: #343a40;
      color: #fff;
      padding: 20px 0;
      text-align: center;
    }
  </style>
</head>
<body>
  <?php include("include/header.php"); ?>

  <main>
    <section class="hero-story">
      <div class="hero-story-content text-center">
        <h1>Explore Our Story</h1>
        <p>Discover the journey that transformed our hospital into a center of excellence</p>
      </div>
    </section>

    <section class="container timeline" id="timeline">
      <div class="timeline-item left">
        <h4>1990</h4>
        <p>Our hospital was founded with a commitment to deliver compassionate care and innovative medical practices to our community.</p>
      </div>
      <div class="timeline-item right">
        <h4>2000</h4>
        <p>We expanded our facilities and introduced advanced diagnostic technologies, setting new standards in patient care.</p>
      </div>
      <div class="timeline-item left">
        <h4>2010</h4>
        <p>We launched our patient portal, making healthcare more accessible and empowering our patients with digital tools.</p>
      </div>
      <div class="timeline-item right">
        <h4>2020</h4>
        <p>We adapted to modern challenges by implementing cutting-edge treatments and rigorous safety protocols during the global pandemic.</p>
      </div>
      <div class="timeline-item left">
        <h4>Future</h4>
        <p>Continuing our legacy of innovation, we are investing in research and new technologies to shape the future of healthcare.</p>
      </div>
    </section>
    <section class="commitment-section">
      <div class="container">
        <h2>Our Commitment</h2>
        <p>
          Every milestone in our journey reflects our dedication to excellence in healthcare. We continuously strive to innovate, improve, and provide personalized care to every patient.
        </p>
      </div>
    </section>
  </main>

  <footer>
    <p class="mb-0">&copy; <span id="year"></span> Hospital System. All rights reserved.</p>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById("year").textContent = new Date().getFullYear();
  </script>
</body>
</html>
