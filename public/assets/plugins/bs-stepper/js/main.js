var stepper1
var stepper2
//var stepper3
var stepper4
var stepperForm

 document.addEventListener('DOMContentLoaded', function () {
  // This file is included globally, so guard each init.
  var el1 = document.querySelector('#stepper1')
  if (el1) stepper1 = new Stepper(el1)

  var el2 = document.querySelector('#stepper2')
  if (el2) {
    stepper2 = new Stepper(el2, { linear: false })
  }

  var el3 = document.querySelector('#stepper3')
  if (el3) stepper3 = new Stepper(el3)

  var stepperFormEl = document.querySelector('#stepperForm')
  if (!stepperFormEl) return

  stepperForm = new Stepper(stepperFormEl, { animation: true })

  var btnNextList = [].slice.call(document.querySelectorAll('.btn-next-form'))
  var stepperPanList = [].slice.call(stepperFormEl.querySelectorAll('.bs-stepper-pane'))
  var inputMailForm = document.getElementById('inputMailForm')
  var inputPasswordForm = document.getElementById('inputPasswordForm')
  var form = stepperFormEl.querySelector('.bs-stepper-content form')

  btnNextList.forEach(function (btn) {
    btn.addEventListener('click', function () {
      stepperForm.next()
    })
  })

  stepperFormEl.addEventListener('show.bs-stepper', function (event) {
    if (!form) return
    form.classList.remove('was-validated')
    var nextStep = event.detail.indexStep
    var currentStep = nextStep

    if (currentStep > 0) {
      currentStep--
    }

    var stepperPan = stepperPanList[currentStep]
    if (!stepperPan) return

    var mailEmpty = stepperPan.getAttribute('id') === 'test-form-1' && (!inputMailForm || !inputMailForm.value.length)
    var passEmpty = stepperPan.getAttribute('id') === 'test-form-2' && (!inputPasswordForm || !inputPasswordForm.value.length)

    if (mailEmpty || passEmpty) {
      event.preventDefault()
      form.classList.add('was-validated')
    }
  })
})
