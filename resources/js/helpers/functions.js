import axios from 'axios'
import { ref, computed } from 'vue'

// Reactive variables for managing the state of the component
export const dialog    = ref(false) // Controls the visibility of the modal dialog
export const step      = ref(0) // Tracks the current step in the process
export const file      = ref(null) // Holds the currently selected file
export const summary   = ref([]) // Stores the summary of errors and other data
export const isValid   = ref(false) // Indicates if the current state is valid
export const fileInput = ref(null) // Reference to the file input element
export const previous  = ref(true) // Indicates if the previous button should be enabled
export const finished  = ref(true) // Indicates if the process is finished

// Array defining the steps in the process
const items = [
    { title: 'Step 1', value: 0, subtitle: 'Download template', valid: true },
    { title: 'Step 2', value: 1, subtitle: 'Upload database', valid: true },
    { title: 'Step 3', value: 2, subtitle: 'Import summary', valid: true },
    { value: 3 }
]

// Reactive array for steps
export const steps = ref(items)

// Allowed MIME types for file uploads
const mimeTypes = ['text/csv', 'application/vnd.ms-excel']

// Function to add a file type error message to the summary
const fileTypeError = (message) => {
  // Check if the error message already exists
  const errorExists = summary.value.some(error => error.messages.includes(message))

  if (!errorExists) 
  {
    // Reset errors and add the new error message
    summary.value.errors = []
    summary.value.errors.push({ messages: [message] })
  }
}

// Opens the modal dialog and removes any completed step classes
export const openModal = () => {
  dialog.value = true
  removeCompletedClassList()
}

// Closes the modal dialog, resets the step, and validates steps
export const closeModal = () => {
  dialog.value = false
  step.value = 0
  isValid.value = false
  steps.value.forEach(item => {
    if (!item.valid) {
      item.valid = true
    }
  })
}

// Moves to the previous step, marks the current step as valid, and updates button states
export const backStep = () => {
  step.value--
  isValid.value = true
  previous.value = false
  removeCompletedClassList(step.value + 1)
}

// Moves to the next step if the current state is valid and updates button states
export const nextStep = () => {
  if (validate()) {
    step.value++
  }
  isValid.value = false

  if (step.value >= 2) {
    isValid.value = true
  }
  previous.value = true
}

// Validates the current step and updates step validity
const validate = () => {
  removeCompletedClassList(step.value + 1)
  return steps.value[step.value] && (steps.value[step.value].valid = isValid.value)
}

// Downloads the template file and triggers a download prompt
export const downloadTemplate = async () => {
  const templateUrl = `${location.origin}/templates/media.csv`

  try {
    const response = await axios.get(templateUrl, { responseType: 'blob' })
    if (response.status === 200 && mimeTypes.includes(response.data.type)) {
      saveAs(response.data, 'template.csv')
      isValid.value = true
    } else {
      console.error('File does not exist or unsupported MIME type.')
    }
  } catch (error) {
    console.error('Error downloading the template:', error)
  }
}

// Handles file upload events and validates file format
export const handleFileUpload = async (event) => {
  const files = event.target.files
  if (files.length > 0 && mimeTypes.includes(files[0].type)) {
    file.value = files[0]
    await uploadFile()
  } else {
    if (files.length > 0) {  
      fileTypeError('File format not allowed. Allowed formats are CSV or XLS.')
    }
  }
}

// Handles file drop events and validates file format
export const handleFileDrop = async (event) => {
  const droppedFiles = event.dataTransfer.files
  if (droppedFiles.length > 0 && mimeTypes.includes(droppedFiles[0].type)) {
    file.value = droppedFiles[0]
    await uploadFile()
  } else {
    if (droppedFiles.length > 0) {  
      fileTypeError('File format not allowed. Allowed formats are CSV or XLS.')
    }
  }
}

// Triggers the file input element to open the file picker dialog
export const triggerFileInput = () => {
  fileInput.value.click()
}

// Uploads the selected file and processes the response
export const uploadFile = async () => {
  if (!file.value) return

  const formData = new FormData()
  formData.append('file', file.value)

  try {
    const response = await axios.post('/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (response.status === 200) {
      window.Echo.channel('import-media').listen('ImportMedia', async (e) => {
        summary.value = e.summary
        isValid.value = !summary.value.errors
      })
    }
  } catch (error) {
    console.error('Error uploading file:', error.response?.data || error.message)
  }
}

// Removes the completed class from stepper items, optionally for a specific step
export const removeCompletedClassList = (key = null) => {
  setTimeout(() => {
    const steppers = document.querySelectorAll('.v-stepper-item')
    if (key !== null) {
      steppers[key]?.classList.remove('v-stepper-item--complete')
    } else {
      steppers.forEach(item => item.classList.remove('v-stepper-item--complete'))
    }
  }, 0)
}
