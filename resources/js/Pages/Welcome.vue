<template>
  <div class="min-h-screen bg-gray-50 dark:bg-zinc-900 text-gray-800 dark:text-white flex flex-col">
    <header class="bg-white dark:bg-zinc-800 shadow">
      <div class="max-w-7xl mx-auto px-4 py-5 flex justify-between items-center">
        <h1 class="text-2xl font-bold">Veterinaria Fumican 🐾</h1>
        <a :href="route('login')" class="text-emerald-600 hover:text-emerald-800 font-semibold">
          Iniciar sesión
        </a>
      </div>
    </header>

    <section>
      <img src="https://res.cloudinary.com/dnkvrqfus/image/upload/v1751250681/lvag6adgiw08rwmc3b9f.png"
        alt="Veterinaria portada" class="w-full h-auto object-cover" />
    </section>

    <section class="text-center py-16 px-4">
      <h2 class="text-4xl font-bold mb-4">Bienvenido a nuestra veterinaria</h2>
      <p class="text-gray-600 dark:text-gray-300 text-lg mb-8">
        Cuidamos con amor a tus mejores amigos de cuatro patas 🐶🐱
      </p>
      <button @click="showForm = !showForm"
        class="bg-emerald-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-emerald-700 transition">
        {{ showForm ? 'Cerrar reserva' : 'Solicitar una cita' }}
      </button>
    </section>

    <section v-if="showForm" class="max-w-3xl mx-auto px-6 pb-20">
      <div class="bg-white dark:bg-zinc-800 shadow-lg rounded-lg p-8">
        <h3 class="text-2xl font-semibold mb-6">Reserva tu cita</h3>
        <form class="space-y-5">
          <div class="grid md:grid-cols-2 gap-4">
            <input v-model="appointmentForm.name" type="text" placeholder="Nombre completo" required class="input" />
            <input v-model="appointmentForm.phone" type="tel" placeholder="Teléfono" required class="input" />
            <input v-model="appointmentForm.email" type="email" placeholder="Correo electrónico" required
              class="input" />
            <input v-model="appointmentForm.petName" type="text" placeholder="Nombre de tu mascota" required
              class="input" />
          </div>

          <div class="grid md:grid-cols-2 gap-4">
            <select v-model="appointmentForm.service" required class="input">
              <option value="">Selecciona un servicio</option>
              <option value="consulta">Consulta</option>
              <option value="vacunación">Vacunación</option>
              <option value="desparasitación">Desparasitación</option>
            </select>
            <input v-model="appointmentForm.date" type="date" required class="input" />
          </div>

          <div>
            <label class="block mb-1 text-sm">Selecciona un horario</label>
            <div class="flex flex-wrap gap-3">
              <button v-for="time in availableTimeSlots" :key="time" type="button"
                @click="appointmentForm.timeSlot = time" :class="['px-4 py-2 rounded border text-sm',
                  appointmentForm.timeSlot === time
                    ? 'bg-emerald-600 text-white border-emerald-700'
                    : 'bg-white dark:bg-zinc-700 text-gray-700 dark:text-white hover:bg-gray-100']">
                {{ time }}
              </button>
            </div>
          </div>

          <textarea v-model="appointmentForm.comment" rows="3" placeholder="Comentarios adicionales"
            class="input resize-none w-full"></textarea>

          <button type="button" @click="openPaymentModal"
            class="bg-emerald-600 text-white px-6 py-3 rounded-md hover:bg-emerald-700 transition">
            Pagar consulta
          </button>
        </form>

        <!-- Modal de pago QR -->
        <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center">
          <div class="bg-white dark:bg-zinc-800 rounded-lg p-6 w-full max-w-md relative shadow-xl">
            <button @click="closePaymentModal"
              class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-white text-xl">&times;</button>

            <h3 class="text-xl font-semibold mb-4">Escanea y paga con QR</h3>

            <div class="flex justify-center mb-4">
              <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://micuenta.vet/pago123"
                alt="Código QR para pagar" class="w-48 h-48 rounded shadow-lg border" />
            </div>

            <div v-if="paymentStatus === 'verificando'" class="text-center text-sm text-gray-500">
              <span class="inline-flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg"
                  fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                </svg>
                Verificando pago...
              </span>
            </div>

            <div v-else-if="paymentStatus === 'completado'" class="text-center text-emerald-600 font-semibold">
              ✅ Pago verificado correctamente
            </div>

            <div v-else class="mt-4">
              <button @click="confirmPayment"
                class="bg-emerald-600 text-white px-4 py-2 rounded w-full hover:bg-emerald-700">
                Confirmar pago
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-white dark:bg-zinc-800 py-16">
      <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-10 px-6 items-center">
        <img src="https://res.cloudinary.com/dnkvrqfus/image/upload/v1751251720/oulejbsmajreavobbts5.jpg"
          alt="Mascota feliz" class="rounded-lg shadow-lg w-full object-cover max-h-[400px]">
        <div>
          <h3 class="text-2xl font-bold mb-4">Amor, cuidado y compromiso</h3>
          <p class="text-lg text-gray-600 dark:text-gray-300">
            En Veterinaria Fumican, nuestro objetivo es ofrecer atención médica y emocional de calidad a tus
            mascotas.
            Contamos con un equipo profesional apasionado por el bienestar animal, tecnología moderna y un
            ambiente amigable.
          </p>
        </div>
      </div>
    </section>

    <section class="bg-gray-100 dark:bg-zinc-900 py-16">
      <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-10 px-6 items-center">
        <div>
          <h3 class="text-2xl font-bold mb-4">Servicios personalizados</h3>
          <p class="text-lg text-gray-600 dark:text-gray-300">
            Desde consultas médicas, vacunas, cirugías menores hasta peluquería canina. Tu mascota recibirá
            la mejor atención.
            Agenda fácilmente tu cita y evita esperas innecesarias.
          </p>
        </div>
        <img src="https://res.cloudinary.com/dnkvrqfus/image/upload/v1751251719/hc17jjagz7rau1mmsmag.jpg"
          alt="Consulta veterinaria" class="rounded-lg shadow-lg w-full object-cover max-h-[400px]">
      </div>
    </section>

    <FwbToast v-if="showToast" :type="toastType" class="fixed top-5 right-5 z-50">
      {{ toastMsg }}
    </FwbToast>

    <div v-if="loadingPdf" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-zinc-800 p-6 rounded-lg flex items-center gap-3 shadow-lg">
        <svg class="animate-spin h-6 w-6 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none"
          viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
        <span class="text-gray-700 dark:text-white">Generando PDF de reserva...</span>
      </div>
    </div>


    <footer class="bg-zinc-800 text-white mt-auto">
      <div class="max-w-7xl mx-auto px-6 py-8 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
          <h4 class="font-bold mb-2">Veterinaria Fumican</h4>
          <p class="text-sm">Cuidamos a tu mascota como si fuera parte de nuestra familia.</p>
        </div>
        <div>
          <h4 class="font-bold mb-2">Contacto</h4>
          <p class="text-sm">📞 (591) 700-00000</p>
          <p class="text-sm">📍 Cambódromo, 5to anillo, Santa Cruz de la Sierra</p>
          <p class="text-sm">✉ contacto@fumican.com</p>
        </div>
        <div>
          <h4 class="font-bold mb-2">Horario</h4>
          <p class="text-sm">Lunes a Viernes: 9:00 - 18:00</p>
          <p class="text-sm">Sábado: 9:00 - 13:00</p>
          <p class="text-sm">Domingo: Cerrado</p>
        </div>
      </div>
      <div class="text-center text-sm bg-zinc-900 py-4">
        &copy; 2025 Veterinaria Fumican. Todos los derechos reservados.
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { FwbToast } from "flowbite-vue";

const showForm = ref(false)
const showPaymentModal = ref(false)
const paymentStatus = ref('')
const loadingPdf = ref(false)

const showToast = ref(false)
const toastMsg = ref('')
const toastType = ref('success')

const appointmentForm = ref({
  name: '',
  phone: '',
  email: '',
  petName: '',
  service: '',
  date: '',
  timeSlot: '',
  comment: ''
})

function showToastMessage(message, type = 'success') {
  toastMsg.value = message
  toastType.value = type
  showToast.value = true
  setTimeout(() => { showToast.value = false }, 3000)
}

const availableTimeSlots = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00']

function openPaymentModal() {
  const data = appointmentForm.value
  if (
    data.name && data.phone && data.email && data.petName &&
    data.service && data.date && data.timeSlot
  ) {
    showPaymentModal.value = true
  } else {
    alert('Por favor, completa todos los campos obligatorios antes de pagar.')
  }
}

async function confirmPayment() {
  paymentStatus.value = 'verificando'

  setTimeout(() => {
    paymentStatus.value = 'completado'

    setTimeout(async () => {
      closePaymentModal()

      loadingPdf.value = true 
      await downloadReservationPdf()
      loadingPdf.value = false 

      showToastMessage('Reserva realizada con éxito', 'success')

      appointmentForm.value = {
        name: '', phone: '', email: '', petName: '',
        service: '', date: '', timeSlot: '', comment: ''
      }
      showForm.value = false
    }, 3000)
  }, 5000)
}


function closePaymentModal() {
  showPaymentModal.value = false
  paymentStatus.value = ''
}

async function downloadReservationPdf() {
  try {
    const response = await fetch('/reserve-pdf', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify(appointmentForm.value)
    });

    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'reserva.pdf';
    document.body.appendChild(a);
    a.click();
    a.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error al descargar el PDF:', error);
    showToastMessage('Ocurrió un error al generar el PDF', 'danger');
  }
}
</script>

<style scoped>
.input {
  @apply w-full px-4 py-2 rounded-md border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500;
}
</style>
