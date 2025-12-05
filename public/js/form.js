document.addEventListener("DOMContentLoaded", () => {
  const inputs = document.querySelectorAll("[data-fun-input]");
  inputs.forEach((input) => {
    input.addEventListener("focus", () => {
      input.classList.add("pulsing");
    });
    input.addEventListener("blur", () => {
      input.classList.remove("pulsing");
    });
  });
});

// Effet pulsation
const style = document.createElement("style");
style.textContent = `
  @keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(255,94,43,0.6); }
    70% { box-shadow: 0 0 0 10px rgba(255,94,43,0); }
    100% { box-shadow: 0 0 0 0 rgba(255,94,43,0); }
  }
  .fun-input.pulsing {
    animation: pulse 1.5s infinite;
  }
`;
document.head.appendChild(style);
