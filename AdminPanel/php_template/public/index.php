<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Submit Request - ONGC</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <canvas id="magic-bg"></canvas>

  <div class="container">
    <h1>Submit Request</h1>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert success">Request submitted successfully.</div>
    <?php elseif (isset($_GET['error'])): ?>
      <div class="alert error">Error submitting request. Please try again.</div>
    <?php endif; ?>

    <form method="POST" action="submit_request.php">
      <div class="form-block">
        <input type="text" name="cpf" placeholder="CPF" required />
        <input type="text" name="name" placeholder="Name" required />
        <input type="text" name="designation" placeholder="Designation" required />
        <input type="text" name="mobile" placeholder="Mobile" required />
        <input type="text" name="section" placeholder="Section" required />
        <input type="text" name="subsection" placeholder="Subsection" />
        <input type="text" name="ext" placeholder="Extension" />
        <input type="text" name="direct" placeholder="Direct" />
        <input type="date" name="dob" placeholder="Date of Birth" />
        <input type="date" name="dor" placeholder="Date of Retirement" />
        <input type="text" name="level" placeholder="Level" />
      </div>
      <button type="submit">Submit Request</button>
    </form>
  </div>

  <script>
    const canvas = document.getElementById('magic-bg');
    const ctx = canvas.getContext('2d');
    let particles = [];
    canvas.style.position = 'fixed';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.zIndex = '-1';
    canvas.style.pointerEvents = 'none';

    function resizeCanvas() {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    function Particle(x, y) {
      this.x = x;
      this.y = y;
      this.size = Math.random() * 3 + 1;
      this.speedX = (Math.random() - 0.5) * 1.5;
      this.speedY = (Math.random() - 0.5) * 1.5;
      this.color = `rgba(200, 220, 255, 0.5)`;
    }

    Particle.prototype.update = function () {
      this.x += this.speedX;
      this.y += this.speedY;
      this.size *= 0.98;
    };

    Particle.prototype.draw = function () {
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
      ctx.fillStyle = this.color;
      ctx.fill();
    };

    function handleParticles() {
      for (let i = 0; i < particles.length; i++) {
        particles[i].update();
        particles[i].draw();
        if (particles[i].size < 0.5) {
          particles.splice(i, 1);
          i--;
        }
      }
    }

    window.addEventListener('mousemove', function (event) {
      for (let i = 0; i < 2; i++) {
        particles.push(new Particle(event.x, event.y));
      }
    });

    function animate() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      handleParticles();
      requestAnimationFrame(animate);
    }

    animate();
  </script>
</body>
</html>
