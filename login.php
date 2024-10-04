<?php include_once "./header.php"; ?>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="wrapper bg-white p-8 shadow-lg rounded-lg max-w-md w-full">
    <section class="form Login">
      <header class="text-2xl font-bold text-center text-blue-600 mb-6">Login (User)</header>

      <form action="#">
        <div class="error-txt text-red-500 mb-4 hidden"></div>

        <div class="field input mb-4">
          <label for="email" class="text-gray-700">Email Address</label>
          <input type="text" name="email" id="email" placeholder="Enter your email address"
                 class="mt-2 p-3 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="field input mb-4 relative">
          <label for="password" class="text-gray-700">Password</label>
          <input type="password" name="password" id="password" placeholder="Enter your password"
                 class="mt-2 p-3 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <i class="fas fa-eye absolute top-10 right-3 text-gray-500 cursor-pointer"></i>
        </div>

        <div class="field button">
          <input type="submit" value="Continue"
                 class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
        </div>
      </form>

      <div class="link text-center mt-6 text-gray-700">
        Not yet signed up? <a href="index.php" class="text-blue-600 hover:underline">Signup now</a>
      </div>
    </section>
  </div>

  <script src="javascript/pass-show-hide.js"></script>
  <script src="javascript/login.js"></script>
</body>
</html>
