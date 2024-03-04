
$(document).ready(function () {
    $("#reservation_date").datepicker({
      format: "dd-mm-yyyy",
      startDate: "today",
      maxViewMode: 1,
      language: "fr",
      daysOfWeekDisabled: "0,1",
      todayHighlight: true,
      endDate: "+1m +1w",
      autoclose: true,
    });
  });
  $("#reservation_date").on("change", updateTimeSlots);
  updateTimeSlots();
  
  let timeSelectHour = document.getElementById("reservation_time_hour");
  let timeSelectMinute = document.getElementById("reservation_time_minute");
  let reservationTime = document.getElementById("reservation_time");
  let howManyGuestSelector = document.getElementById("reservation_howManyGuest");
  let reservationForm = document.getElementById("reservation_form")
  let conform = true;
  
  function updateTimeSlots() {
    let dateInput = document.getElementById("reservation_date");
    fetch("/api?date=" + dateInput.value)
      .then(function (response) {
        if (!response.ok) {
          throw new Error("Une erreur est survenue.");
        }
        return response.json();
      })
      .then(function (data) {
        console.log(data);
        let hourSlot = [12,13,19,20];
        let minutesSlot = [0, 15, 30, 45];
        let messageElement = document.getElementById("messageReservation");
  
        if (messageElement) {
          messageElement.parentNode.removeChild(messageElement);
          timeSelectHour.disabled = false;
          timeSelectMinute.disabled = false;
          howManyGuestSelector.disabled = false;
          conform = true;
        }
        if ((data.maxReservationLunch <= 0 || data.maxSeatLunch <= 0) && (data.maxReservationDiner <= 0 || data.maxSeatDiner <= 0)) {
          // Si aucune réservation n'est possible ni pour le déjeuner ni pour le dîner, désactiver les sélecteurs d'heure et de minute
          timeSelectHour.disabled = true;
          timeSelectMinute.disabled = true;
          howManyGuestSelector.disabled = true;
          let messageElement = document.createElement("p");
          messageElement.id = "messageReservation";
          messageElement.textContent =
            "Les réservations sont complètes pour le déjeuner et le dîner.";
          reservationTime.parentNode.appendChild(messageElement); // Ajouter le message à côté des sélecteurs
          conform = false;
        } else if (data.maxReservationLunch <= 0 || data.maxSeatLunch <= 0) {
          // Si aucune réservation n'est possible pour le déjeuner, définir les créneaux horaires sur le dîner
          updateNumberGuest(data.maxReservationDiner)
        } else if (data.maxReservationDiner <= 0 || data.maxSeatDiner <= 0) {
          // Si aucune réservation n'est possible pour le dîner, définir les créneaux horaires sur le déjeuner
          updateNumberGuest(data.maxReservationLunch)
        }

        timeSelectHour.innerHTML = "";
        timeSelectMinute.innerHTML = "";

        if(data.hour >= "12" && data.hour <= "14"){
            updateNumberGuest(data.maxSeatLunch+data.howManyGuest)
        }else if(data.hour >= "19" && data.hour <= "21"){
            updateNumberGuest(data.maxSeatDiner+data.howManyGuest)
        }
        hourSlot.map

        hourSlot.forEach((element) => {
          let option = document.createElement("option");
          option.value = element;
          option.text = element;
          timeSelectHour.add(option);
        });
        minutesSlot.forEach((element) => {
          let option = document.createElement("option");
          option.value = element;
          option.text = element;
          if (element == 0) {
            option.text = "00";
          }
          timeSelectMinute.add(option);
        });
        
        if(data.hour){
            timeSelectHour.value=data.hour
        }
        
        if(data.minute =="00"){
          timeSelectMinute.value=0
          timeSelectMinute.text= "00"
        }else if(data.minute){
          timeSelectMinute.value=data.minute
        }
        if(data.length ==0){
            updateNumberGuest(40)
        }
        timeSelectHour.addEventListener("change", function () {
            if (timeSelectHour.value < 14) {
              updateNumberGuest(data.maxSeatLunch);
            } else {
              updateNumberGuest(data.maxSeatDiner);
            }
          })
      })
      .catch(function (error) {
        console.error(error);
      });
  }
  
  function updateNumberGuest(maxSeatAvailable) {
    for (let i = howManyGuestSelector.options.length - 1; i >= 0; i--) {
      let optionValue = parseInt(howManyGuestSelector.options[i].value);
      if (optionValue > maxSeatAvailable) {
        howManyGuestSelector.options[i].disabled = true; // Désactivez l'option si elle nécessite plus de places disponibles que ce qui est disponible
      } else {
        howManyGuestSelector.options[i].disabled = false; // Sinon, activez l'option
      }
    }
  }
  
    reservationForm.addEventListener("submit", function (event) {
      if (!conform) {
        event.preventDefault();
      }
      let selectedOption = howManyGuestSelector.options[howManyGuestSelector.selectedIndex];
      if (selectedOption.disabled) {
          event.preventDefault();
          alert("Le nombre de restant de place ne permet cette modification");
      }
    });
    