var swiper = new Swiper(".home-slider", {
  loop: true,
  effect: "coverflow",
  spaceBetween: 30,
  grabCursor: true,
  coverflowEffect: {
    rotate: 50,
    stretch: 0,
    depth: 100,
    modifier: 1,
    slideShadows: false,
  },
  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },
});

var swiper = new Swiper(".team-slider", {
  grabCursor: true,
  centeredSlides: true,
  spaceBetween: 20,
  loop: true,
  autoplay: {
    delay: 3000,
    disableOnInteraction: false,
  },
  pagination: {
    el: ".swiper-pagination",
    clickable: true,
  },
  breakpoints: {
    0: {
      slidesPerView: 1,
    },
    768: {
      slidesPerView: 2,
    },
    1024: {
      slidesPerView: 3,
    },
  },
});

var swiper = new Swiper(".review-slider", {
  grabCursor: true,
  centeredSlides: true,
  spaceBetween: 20,
  loop: true,
  autoplay: {
    delay: 4000,
    disableOnInteraction: false,
  },
  pagination: {
    el: ".swiper-pagination",
    clickable: true,
  },
  breakpoints: {
    0: {
      slidesPerView: 1,
    },
    768: {
      slidesPerView: 2,
    },
    1024: {
      slidesPerView: 3,
    },
  },
});

var swiper = new Swiper(".cw-gallery-slider", {
  loop: true,
  effect: "coverflow",
  slidesPerView: "auto",
  centeredSlides: true,
  grabCursor: true,
  coverflowEffect: {
    rotate: 0,
    stretch: 0,
    depth: 100,
    modifier: 2,
    slideShadows: true,
  },
  pagination: {
    el: ".swiper-pagination",
  },
});

document.querySelectorAll(".faq .box-container .box h3").forEach((headings) => {
  headings.onclick = () => {
    headings.parentElement.classList.toggle("active");
  };
});

function display(value) {
  document.getElementById("result").value += value;
}

function clearscreen() {
  document.getElementById("result").value = "";
}

function calculate() {
  var p = document.getElementById("result").value;
  var q = eval(p);
  document.getElementById("result").value = q;
}

//function to delete a single value

function deletex() {
  var value = document.getElementById("result").value;
  document.getElementById("result").value = value.substr(0, value.length - 1);
}

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("buy-form").addEventListener("submit", (event) => {
    event.preventDefault();

    const formData = new FormData(event.target);

    fetch("storetrade.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.text())
      .then((data) => {
        if (data === "success") {
          updateTradeTable();
        } else {
          alert("Trade successfully added"); // Display error message

          header("Refresh: 2;");
        }
      });
  });

  document.getElementById("sell-form").addEventListener("submit", (event) => {
    event.preventDefault();

    const formData = new FormData(event.target);

    fetch("storetrade.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.text())
      .then((data) => {
        if (data === "success") {
          updateTradeTable();
        } else {
          alert("Trade successfully added"); // Display error message

          header("Refresh: 2;");
        }
      });
  });

  function updateTradeTable() {
    fetch("fetchtrades.php") // Make sure this script returns JSON data
      .then((response) => response.json())
      .then((trades) => {
        const tableBody = document.querySelector("#trade-table tbody");
        tableBody.innerHTML = ""; // Clear existing rows

        trades.forEach((trade) => {
          const row = document.createElement("tr");
          row.innerHTML = `
                  <td>${trade.trade_type}</td>
                  <td>${trade.asset}</td>
                  <td>${trade.lot_size.toFixed(2)}</td>
                  <td>${trade.entry_price.toFixed(2)}</td>
                  <td>$${trade.amount.toFixed(2)}</td>
                  <td>${new Date(trade.trade_date).toLocaleString()}</td>
              `;
          tableBody.appendChild(row);
        });
      });
  }
});

// Fade out the notification after 3 seconds
setTimeout(function () {
  const notification = document.getElementById("verification-notification");
  if (notification) {
    notification.classList.add("fade-out");
    // Optionally hide the notification completely after fade-out
    setTimeout(() => {
      notification.style.display = "none";
    }, 500); // match this duration with the CSS transition duration
  }
}, 5000); // 3 seconds
