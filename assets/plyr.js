import Plyr from "plyr";
  // Initialisez Plyr sur votre vidéo
  const player = new Plyr("#player");  
  // Récupérez l'élément de la modal
const modal = document.getElementById("exampleModal");
const playBtn = document.querySelector('.play-btn');

playBtn.addEventListener('click',(e)=>{
  player.play();
})

document.addEventListener("DOMContentLoaded", function () {
  // Écoutez l'événement de fermeture de la modal
  modal.addEventListener("hidden.bs.modal", function () {
    // Arrêtez la lecture de la vidéo
    player.stop();
  });
});