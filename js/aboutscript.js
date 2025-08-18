function scrollPhones(direction) {
  const container = document.getElementById("phoneContainer");
  const scrollAmount = 300; // pixel to scroll
  const scrollOptions = {
    left: container.scrollLeft + direction * scrollAmount,
    behavior: "smooth"
  };
  container.scrollTo(scrollOptions);
}


// Drag scroll with mouse
const phoneContainer = document.getElementById("phoneContainer");

let isDown = false;
let startX;
let scrollLeft;

phoneContainer.addEventListener("mousedown", (e) => {
  isDown = true;
  phoneContainer.classList.add("dragging");
  startX = e.pageX - phoneContainer.offsetLeft;
  scrollLeft = phoneContainer.scrollLeft;
});

phoneContainer.addEventListener("mouseleave", () => {
  isDown = false;
  phoneContainer.classList.remove("dragging");
});

phoneContainer.addEventListener("mouseup", () => {
  isDown = false;
  phoneContainer.classList.remove("dragging");
});

phoneContainer.addEventListener("mousemove", (e) => {
  if (!isDown) return;
  e.preventDefault();
  const x = e.pageX - phoneContainer.offsetLeft;
  const walk = (x - startX) * 2; // scroll speed
  phoneContainer.scrollLeft = scrollLeft - walk;
});
