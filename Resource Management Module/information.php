<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>More Information | Hospital System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      margin: 0;
      padding: 0;
    }
    .hero {
      background: url('img/me.jpg') center/cover no-repeat;
      color: #fff;
      padding: 80px 0;
      text-align: center;
      position: relative;
    }
    .hero-overlay {
      background: rgba(0,0,0,0.5);
      position: absolute;
      top: 0; right: 0; bottom: 0; left: 0;
    }
    .hero-content {
      position: relative;
      z-index: 2;
    }
    .faq-question {
      font-weight: 600;
      margin-top: 1rem;
    }
    .faq-answer {
      margin-left: 1.2rem;
      margin-bottom: 1rem;
    }
    .section-title {
      font-weight: 600;
      margin-bottom: 1rem;
    }
    footer {
      background: #343a40;
      color: #fff;
      padding: 20px 0;
      text-align: center;
    }
  </style>
</head>
<body>

<!-- Hero Section -->
<section class="hero position-relative">
  <div class="hero-overlay"></div>
  <div class="hero-content container text-center text-white">
    <h1 class="display-4">Welcome to Our Hospital</h1>
    <p class="lead">Your Health is Our Priority</p>
    <a href="index.php" class="btn btn-light mt-3">Back to Home</a>
  </div>
</section>
<div class="container my-5">
  <div class="row">
    <div class="col-md-12">
      <h2 class="section-title">About Our Hospital</h2>
      <p>
        Our hospital is dedicated to providing comprehensive healthcare services 
        to the community. With state-of-the-art facilities and a compassionate staff, 
        we are committed to ensuring that every patient receives top-notch medical care 
        in a safe and welcoming environment.
      </p>
      <p>
        Whether you’re visiting us for a routine check-up or specialized treatment, 
        we strive to make your experience as comfortable as possible. Our hospital 
        offers a wide range of services including general medicine, emergency care, 
        surgery, maternity, pediatrics, and more.
      </p>
    </div>
  </div>
  <div class="row mt-5">
    <div class="col-md-12">
      <h2 class="section-title">Frequently Asked Questions (FAQ)</h2>
      <div class="faq-question">
        <i class="fas fa-question-circle"></i> How do I book an appointment?
      </div>
      <div class="faq-answer">
        You can book an appointment by calling our front desk at +234 808 155 7258, or by using 
        our online booking system available on our official website.
      </div>

      <div class="faq-question">
        <i class="fas fa-question-circle"></i> What are the visiting hours?
      </div>
      <div class="faq-answer">
        Visiting hours are generally from 8:00 AM to 8:00 PM. However, some departments have 
        specific restrictions. Please check with the ward or department for details.
      </div>

      <div class="faq-question">
        <i class="fas fa-question-circle"></i> Do you accept health insurance?
      </div>
      <div class="faq-answer">
        Yes, we accept most major health insurance plans. If you have specific questions 
        regarding coverage, please contact our billing department.
      </div>

      <div class="faq-question">
        <i class="fas fa-question-circle"></i> Where can I find parking?
      </div>
      <div class="faq-answer">
        There is a parking garage adjacent to the main building, as well as additional 
        parking in lots B and C. Follow the signs upon arrival, or ask our security 
        personnel for directions.
      </div>
    </div>
  </div>
  <div class="row mt-5">
    <div class="col-md-6">
      <h2 class="section-title">Contact Information</h2>
      <p><strong>Address:</strong> 1234 Health St., Wellness City, Abuja</p>
      <p><strong>Phone:</strong> +234 808 155 7258</p>
      <p><strong>Email:</strong> contact@hospital.com</p>
    </div>
    <div class="col-md-6">
      <h2 class="section-title">Get In Touch</h2>
      <form action="send_message.php" method="post">
        <div class="mb-3">
          <label for="name" class="form-label">Your Name:</label>
          <input type="text" name="name" class="form-control" id="name" placeholder="Full Name">
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Your Email:</label>
          <input type="email" name="email" class="form-control" id="email" placeholder="example@gmail.com">
        </div>
        <div class="mb-3">
          <label for="message" class="form-label">Message:</label>
          <textarea name="message" class="form-control" id="message" rows="4" placeholder="Your questions or comments"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send Inquiry</button>
      </form>
    </div>
  </div>
</div>

<footer>
  <p class="mb-0">© <?php echo date("Y"); ?> Our Hospital. All Rights Reserved.</p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
