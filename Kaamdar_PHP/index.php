<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>KaamDar</title>
    <link rel="stylesheet" href="css/style.css" />
  </head>
  <body>
    <!-- Header / Navbar -->
    <header>
      <nav class="navbar">
        <a href="#" class="nav-logo">
          <h2 class="logo-text">KaamDar</h2>
        </a>
        <ul class="nav-service">
          <button id="service-close-button" class="fas fa-times"></button>
          <li class="nav-item">
            <a href="#" class="nav-link">Home</a>
          </li>
          <li class="nav-item">
            <a href="#about" class="nav-link">About</a>
          </li>
          <li class="nav-item">
            <a href="#services" class="nav-link">Services</a>
          </li>
          <li class="nav-item">
            <a href="#contact" class="nav-link">Contact</a>
          </li>
        </ul>
        <button id="service-open-button" class="fas fa-bars"></button>
      </nav>
    </header>

    <main>
      <!-- Hero section -->
      <section class="hero-section">
        <div class="section-content">
          <div class="hero-details">
            <h2 class="title">KaamDar</h2>
            <h3 class="subtitle">Your Trusted Partner for Finding Skilled Laborers</h3>
            <p class="description">Welcome! Connecting You to the Best Hands for Every Task</p>
            <div class="buttons">
              <!-- Updated "Book Now" Button -->
              <a href="RequesterLogin.php" class="button order-now">Book Now</a>
              <a href="#contact" class="button contact-us">Contact Us</a>
            </div>
          </div>
          <div class="hero-image-wrapper">
            <img src="images/Construction worker-bro.png" alt="Image" class="hero-image" />
          </div>
        </div>
      </section>

      <!-- About section -->
      <section class="about-section" id="about">
        <div class="section-content">
          <div class="about-image-wrapper">
            <img src="images/Construction worker-pana.png" alt="About" class="about-image" />
          </div>
          <div class="about-details">
            <h2 class="section-title">About Us</h2>
            <p class="text">
              KaamDar is a platform dedicated to connecting you with skilled laborers across various fields. We simplify the hiring process by providing a diverse pool of vetted professionals ready to tackle any job. Our mission is to bridge the gap between service seekers and laborers, offering a seamless and secure way to find and hire the right talent. Whether it's home repairs or skilled technical work, KaamDar is your trusted partner for reliable, quality services.
            </p>
            <div class="social-link-list">
              <a href="#" class="social-link"><i class="fa-brands fa-facebook"></i></a>
              <a href="#" class="social-link"><i class="fa-brands fa-instagram"></i></a>
              <a href="#" class="social-link"><i class="fa-brands fa-x-twitter"></i></a>
            </div>
          </div>
        </div>
      </section>

      <!-- Services section -->
      <section class="service-section" id="services">
        <h2 class="section-title">Our Services</h2>
        <div class="section-content">
          <ul class="service-list">
            <li class="service-item">
              <img src="images/builder-construction-vest-orange-helmet-standing-safety-specialist-engineer-industry-architecture-manager-occupation-businessman-job-concept.jpg" alt="Home Repairs" class="service-image" />
              <div class="service-details">
                <h3 class="name">Home Repairs</h3>
                <p class="text">From plumbing to electrical fixes, find skilled laborers for all your home maintenance needs.</p>
                <a href="UserRegistration.php" class="button book-now">Book Now</a>
              </div>
            </li>
            <li class="service-item">
              <img src="images/man-kneeling-down-inspect-pipes-sink.jpg" alt="Construction Services" class="service-image" />
              <div class="service-details">
                <h3 class="name">Construction Services</h3>
                <p class="text">Hire experienced laborers for construction, renovation, and building projects.</p>
                <a href="UserRegistration.php" class="button book-now">Book Now</a>
              </div>
            </li>
            <li class="service-item">
              <img src="images/hardhat-wearing-men-work-together-build-factory-generated-by-ai.jpg" alt="Cleaning Services" class="service-image" />
              <div class="service-details">
                <h3 class="name">Cleaning Services</h3>
                <p class="text">Professional cleaners for residential and commercial spaces, ensuring spotless results.</p>
                <a href="UserRegistration.php" class="button book-now">Book Now</a>
              </div>
            </li>
            <li class="service-item">
              <img src="images/bermix-studio-y9RGeKyVpM8-unsplash.jpg" alt="Painting & Decorating" class="service-image" />
              <div class="service-details">
                <h3 class="name">Painting & Decorating</h3>
                <p class="text">Skilled painters and decorators to refresh your home or business with expert finishes.</p>
                <a href="UserRegistration.php" class="button book-now">Book Now</a>
              </div>
            </li>
            <li class="service-item">
              <img src="images/techinca.jpg" alt="Handyman Services" class="service-image" />
              <div class="service-details">
                <h3 class="name">Handyman Services</h3>
                <p class="text">General laborers for minor repairs, installations, and other household tasks.</p>
                <a href="UserRegistration.php" class="button book-now">Book Now</a>
              </div>
            </li>
            <li class="service-item">
              <img src="images/techinca.jpg" alt="Moving & Packing" class="service-image" />
              <div class="service-details">
                <h3 class="name">Moving & Packing</h3>
                <p class="text">Reliable laborers to assist with packing, moving, and transportation for hassle-free relocations.</p>
                <a href="UserRegistration.php" class="button book-now">Book Now</a>
              </div>
            </li>
          </ul>
        </div>
      </section>

      <!-- Contact section -->
      <section class="contact-section" id="contact">
        <h2 class="section-title">Contact Us</h2>
        <div class="section-content">
          <ul class="contact-info-list">
            <li class="contact-info">
              <i class="fa-solid fa-location-crosshairs"></i>
              <p>Kathmandu, Nepal</p>
            </li>
            <li class="contact-info">
              <i class="fa-regular fa-envelope"></i>
              <p>Kaamdar@gmail.com</p>
            </li>
            <li class="contact-info">
              <i class="fa-solid fa-phone"></i>
              <p>+977-9802543322</p>
            </li>
            <li class="contact-info">
              <i class="fa-regular fa-clock"></i>
              <p>Monday - Friday: 9:00 AM - 5:00 PM</p>
            </li>
            <li class="contact-info">
              <i class="fa-regular fa-clock"></i>
              <p>Saturday: 10:00 AM - 3:00 PM</p>
            </li>
            <li class="contact-info">
              <i class="fa-regular fa-clock"></i>
              <p>Sunday: Closed</p>
            </li>
            <li class="contact-info">
              <i class="fa-solid fa-globe"></i>
              <p>www.KaamDar.com.np</p>
            </li>
          </ul>
          <?php if($error): ?>
              <div class="alert alert-danger">
                  <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
              </div>
              <div class="text-center mt-4">
                  <a href="technician.php" class="btn btn-primary">
                      <i class="fas fa-arrow-left me-2"></i>Return to Technicians
                  </a>
              </div>
          <?php else: ?>
              <form action="contactform.php" method="post" class="contact-form">
                <input type="text" name="name" placeholder="Your name" class="form-input" required />
                <input type="email" name="email" placeholder="Your email" class="form-input" required />
                <input type="text" name="subject" placeholder="Subject" class="form-input" required />
                <textarea name="message" placeholder="Your message" class="form-input" required></textarea>
                <button type="submit" name="submit" class="button submit-button">Submit</button>
              </form>
          <?php endif; ?>
        </div>
      </section>

      <!-- Footer section -->
      <footer class="footer-section">
        <div class="section-content">
          <p class="copyright-text">@ KaamDar 2024</p>
          <div class="social-link-list">
            <a href="#" class="social-link"><i class="fa-brands fa-facebook"></i></a>
            <a href="#" class="social-link"><i class="fa-brands fa-instagram"></i></a>
            <a href="#" class="social-link"><i class="fa-brands fa-x-twitter"></i></a>
          </div>
          <p class="policy-text">
            <a href="#" class="policy-link">Privacy policy</a>
            <span class="separator">•</span>
            <a href="#" class="policy-link">Refund policy</a>
          </p>
        </div>
      </footer>
    </main>

    <!-- Linking Swiper script -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Linking custom script -->
    <script src="script.js"></script>
  </body>
</html>