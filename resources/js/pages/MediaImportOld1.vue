<script setup>
  import { ref, onMounted, watch } from 'vue'
  import axios from 'axios'
  import { saveAs } from 'file-saver'

  const dialog    = ref(false)
  const step      = ref(0)
  const file      = ref(null)
  const summary   = ref([])
  const isValid   = ref(false)
  const fileInput = ref(null)
  const previous  = ref(true)
  const finished  = ref(true)

  const items = [
    { title: 'Step 1', value: 0, subtitle: 'Download template', valid: true },
    { title: 'Step 2', value: 1, subtitle: 'Upload database', valid: true },
    { title: 'Step 3', value: 2, subtitle: 'Import summary', valid: true },
    { value: 3 }
  ]

  const steps = ref(items)

  const mimeTypes = ['text/csv', 'application/vnd.ms-excel']

  let mimeType

  const openModal = () => {
    dialog.value = true // Opens the modal
    removeCompletedClassList()
  }

  const closeModal = () => {
    dialog.value = false // Closes the modal
    step.value = 0
    isValid.value = false
    steps.value.forEach(item => {
      if (!item.valid) 
      {
        item.valid = !item.valid
      }
    })
  }

  const backStep = () => {
    step.value--
    isValid.value = true
    validate()
    previous.value = false
  }

  const nextStep = () => {
    // Increment the step value
    if(validate())
    {
      step.value++
    }
    isValid.value = false

    if(step.value >= 2)
    {
      isValid.value = true
    }
    previous.value = true
  }

  const validate = () => {
    removeCompletedClassList(step.value + 1)
    return steps.value[step.value].valid = isValid.value
  }

  const downloadTemplate = async () => {
    const templateUrl = location.origin + '/templates/media.csv'

    try {
      // Check if file exists
      const response = await axios.get(templateUrl, { responseType: 'blob' })
      
      if(response.status === 200)
      {
        mimeType = response.data.type
      }
      
      if(mimeTypes.includes(mimeType)) 
      {
        // File exists, proceed to download
        const blobResponse = await fetch(templateUrl)
        saveAs(response.data, 'template.csv')
        // Proceed to the next step
        isValid.value = true
      } 
      else 
      {
        // File does not exist, handle the error
        console.error('File does not exist.')
        // Optionally display a message to the user or handle the situation
      }
    } catch (error) {
      console.error('Error checking the file:', error)
      // Handle error here
    }
  }

  const handleFileUpload = async (event) => {
    const files    = event.target.files
    const fileType = files[0].type

    if (files.length > 0 && mimeTypes.includes(fileType)) 
    {
      file.value = event.target.files[0]
      await uploadFile()
    } 
  }

  const handleFileDrop = async (event) => {
    const droppedFiles = event.dataTransfer.files
    const droppedFileType = droppedFiles[0].type

    if (droppedFiles.length > 0 && mimeTypes.includes(droppedFileType)) 
    {
      file.value = droppedFiles[0]
      await uploadFile()
    } 
  }

  const triggerFileInput = () => {
    fileInput.value.click()
  }

  const uploadFile = async () => {
    if (!file.value) {
      // No file to upload
      return
    }

    const formData = new FormData()
    formData.append('file', file.value)

    try {
      const response = await axios.post('/import', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        }
      })
      
      if(response.status === 200)
      {
        window.Echo.channel('import-media')
          .listen('ImportMedia', async (e) => {
            summary.value = e.summary
            
            if(summary.value.errors)
            {
              isValid.value = false
            }
            else
            {
              isValid.value = true
            }
        })
      }

    } catch (error) {
      console.error('Error uploading file:', error.response ? error.response.data : error.message)
    }
  }

  const removeCompletedClassList = (key = null) => {
    setTimeout(() => {

      const steppers = document.querySelectorAll('.v-stepper-item')

      if(key !== null)
      {
        if (steppers[key]) 
        {
          steppers[key].classList.remove('v-stepper-item--complete')
        }
      }
      else
      {
        steppers.forEach(item => {
          item.classList.remove('v-stepper-item--complete')
        })
      }
    }, 0)
  }
  onMounted(() => {
    dialog.value = true
    setTimeout(() => {
      try {
        removeCompletedClassList()
      } catch (error) {
        console.error('Error setting up event listener:', error)
      }
    }, 200)
  })
  watch(isValid, (newIsValid) => {
    if(previous.value)
    {
      if(step.value < 2 && newIsValid)
      {
        nextStep()
        finished.value = false
      }
      if (step.value >= 2 && newIsValid) 
      {
        setTimeout(() => {
          nextStep()
          finished.value = true
        }, 1000)
      }
    }
  })
</script>

<template>
  <v-container>
    <div class="text-center">
      <v-btn v-if="!dialog" @click="openModal">
        Open Media
      </v-btn>
    </div>
    <!-- Modal with v-stepper -->
    <v-dialog v-model="dialog" max-width="800" persistent>
      <v-toolbar flat>
        <v-toolbar-title>Media database import</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon @click="closeModal">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>
      <v-stepper v-model="step">
        <!-- Stepper Header -->
        <v-stepper-header>
          <template v-for="(item, index) in steps" :key="index">
            <v-stepper-item 
              :complete="step > index"
              :rules="[() => !!item.valid]"
              :title="item.title" 
              :subtitle="item.subtitle"
              :value="item.value" 
              :class="{'d-none': item.value === 3}"
              >
            </v-stepper-item>
          </template>
        </v-stepper-header>
        <!-- Stepper Windows -->
        <v-stepper-window>
          <v-stepper-window-item value="0">
            <v-card class="mb-12">
              <v-card-text>
                <h3>Download template</h3>
                <p>Insert your data into the template and upload the file to update the media database.</p>
                <v-btn @click="downloadTemplate">Download</v-btn>
              </v-card-text>
            </v-card>
          </v-stepper-window-item>
          <v-stepper-window-item value="1">
            <v-card class="mb-12">
              <v-card-text>
                <h3>Upload database</h3>
                <div 
                  class="file-drop-area"
                  @drop.prevent="handleFileDrop"
                  @dragover.prevent>
                  <v-icon class="google-doc-icon mb-4" size="36" color="blue">mdi-file-document</v-icon>
                  <h4>Drag & drop .csv or .xls file here</h4>
                  <p>Or, click to upload <a href="#" @click.prevent="triggerFileInput">here</a>.</p>
                </div>
                <input 
                  type="file" 
                  ref="fileInput" 
                  style="display: none;" 
                  @change="handleFileUpload"
                />
                <v-list v-if="summary.errors">
                  <v-list-item v-for="(error, index) in summary.errors" :key="index">
                    <div class="error-container">
                      <ul>
                        <li v-for="msg in error.messages" :key="msg">
                          <strong color="green">Row {{ error.row }}:</strong>
                          <span class="ms-2 error-message">{{msg}}</span>
                        </li>
                      </ul>
                    </div>
                  </v-list-item>
                </v-list>
              </v-card-text>
            </v-card>
          </v-stepper-window-item>
          <v-stepper-window-item value="2">
            <div class="d-flex justify-center align-center stepper-loader">
              <v-progress-circular
                color="primary"
                indeterminate></v-progress-circular>
            </div>
          </v-stepper-window-item>
          <v-stepper-window-item value="3">
            <v-card class="mb-12">
              <v-card-text>
                <h3>Import Summary</h3>
                <p v-if="summary.media">
                  Successfully imported {{ summary.success }} of {{ summary.total }} media. Review the table below for any errors encountered during the import process.
                </p>
                <v-table>
                  <thead>
                    <tr>
                      <th><strong>Row</strong></th>
                      <th><strong>Details</strong></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in summary.media" :key="item.id">
                      <td :class="{'text-red': item.status === 'Import failed', 'text-dark': item.status === 'Imported', 'text-blue': item.status === 'Clone imported'}">
                        {{ index + 1 }}
                      </td>
                      <td :class="{'text-red': item.status === 'Import failed', 'text-dark': item.status === 'Imported', 'text-blue': item.status === 'Clone imported'}">
                        <strong>{{ item.status }}</strong>
                      </td>
                    </tr>
                  </tbody>
                </v-table>
              </v-card-text>
            </v-card>
          </v-stepper-window-item>
        </v-stepper-window>  
        <!-- Stepper Actions -->
        <v-stepper-actions>
            <template #prev>
              <v-btn 
                class="me-2 white--text btn-back"
                outlined
                text="Back"
                @click="backStep"
                :disabled="finished">
              </v-btn>
            </template>
            <template #next>
              <v-btn
                class="bg-green-darken-3"  
                color="white"
                text="Next"
                @click="nextStep">
              </v-btn>
            </template>
        </v-stepper-actions> 
      </v-stepper>
    </v-dialog>
  </v-container>
</template>

<style>
  /**Hide Overfow x in v-stepper header **/
  .v-stepper-header {
    overflow-x: hidden !important;
  }
  /* Styles for the file drop area */
  .file-drop-area {
    border: 2px dashed #ccc; /* Adds a dashed border with a light gray color */
    padding: 20px; /* Adds padding inside the area */
    text-align: center; /* Centers the text inside the area */
    cursor: pointer; /* Changes the cursor to a pointer when hovering over the area */
  }

  /* Styles for error messages */
  .error-message {
    color: red; /* Sets the text color to red for error messages */
  }

  /* Styles for the error container */
  .error-container {
    border: 1px solid red; /* Adds a solid red border around the container */
    padding: 10px; /* Adds padding inside the container */
    border-radius: 4px; /* Rounds the corners of the container */
    background-color: #fdd; /* Sets a light red background color */
  }

  /* Styles for stepper item titles */
  .v-stepper-item .v-stepper-item__title {
    line-height: 2; /* Sets the line height for better spacing */
    text-align: left; /* Aligns the text to the left */
    font-size: 0.875rem;
  }

  /* Styles for subtitle following stepper item subtitles */
  .v-stepper-item .v-stepper-item__subtitle {
    font-weight: bold;
    font-size: 1rem;
  }

  /* Sets the height of the stepper window to 40% of the viewport height and overflow-y scroll */
 .v-stepper-window {
    height: 40vh;  
    overflow-y: scroll !important;
  }

  /* Styles for stepper actions (navigation buttons) */
  .v-stepper .v-stepper-actions {
    justify-content: end; /* Aligns the actions (buttons) to the end of the container */
  }

  .v-stepper-item:not(.v-stepper-item--error):not(.v-stepper-item--complete) .v-stepper-item__avatar.v-avatar,
  .v-stepper-item.v-stepper-item--selected:not(.v-stepper-item--error):not(.v-stepper-item--complete) .v-stepper-item__avatar.v-avatar {
    background: transparent;
    border: 3px solid #0fa30f;
  }

  /* Styles for the stepper item avatar for completed steps */
  .v-stepper-item.v-stepper-item--complete .v-stepper-item__avatar.v-avatar {
    background: #005e00; /* Sets the background color to a dark green */
  }

  /* Styles for icons inside the stepper item avatar */
  .v-stepper-item__avatar.v-avatar .v-icon {
    color: #fff; /* Sets the icon color to white */
  } 
  /* Change border color */
  .v-btn.v-btn--density-default.btn-back {
    border: thin solid;
  }
  /**Styles for Error Stepper Icon**/
  .v-stepper-item--error .v-stepper-item__avatar.v-avatar .v-icon:before {
    font-size: x-large
  }
  /**Styles for Loader**/
  .stepper-loader {
    position: absolute;
    left: 0;
    right: 0;
    height: 100%;
  }
</style>
