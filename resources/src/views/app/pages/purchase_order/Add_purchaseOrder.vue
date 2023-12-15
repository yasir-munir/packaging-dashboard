<template>
  <div class="main-content">
    <breadcumb :page="$t('Purchase Order')" :folder="$t('List')" />
    <div
      v-if="isLoading"
      class="loading_page spinner spinner-primary mr-3"
    ></div>

    <validation-observer ref="Create_Product" v-if="!isLoading">
      <b-form @submit.prevent="Submit_Product" enctype="multipart/form-data">
        <b-row>
          <b-col md="12" class="mb-2">
            <b-card class="mt-3">
              <b-row>
                <!-- Customer -->
                <b-col md="6" class="mb-2">
                  <validation-provider
                    name="Customer"
                    :rules="{ required: true }"
                  >
                    <b-form-group
                      slot-scope="{ valid, errors }"
                      :label="$t('Customer') + ' ' + '*'"
                    >
                      <v-select
                        :class="{ 'is-invalid': !!errors.length }"
                        :state="errors[0] ? false : valid ? true : null"
                        v-model="po.client_id"
                        @input="Selected_customer"
                        :reduce="(label) => label.value"
                        :placeholder="$t('Choose_Customer')"
                        :options="
                          clients.map((clients) => ({
                            label: clients.name,
                            value: clients.id,
                          }))
                        "
                      />
                      <b-form-invalid-feedback>{{
                        errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Order Date -->
                <b-col md="6" class="mb-2">
                  <validation-provider
                    name="Order Date"
                    :rules="{ required: true }"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('Order Date') + ' ' + '*'">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="Name-feedback"
                        label="Order Date"
                        type="date"
                        :placeholder="$t('Order Date')"
                        v-model="po.order_date"
                      ></b-form-input>
                      <b-form-invalid-feedback id="Name-feedback">{{
                        validationContext.errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Export Order -->
                <b-col md="6" class="mb-2">
                  <validation-provider
                    name="Export Order"
                    :rules="{ required: true, min: 3, max: 55 }"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('Export Order') + ' ' + '*'">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="Name-feedback"
                        label="Name"
                        :placeholder="$t('Export Order')"
                        v-model="po.export_order"
                      ></b-form-input>
                      <b-form-invalid-feedback id="Name-feedback">{{
                        validationContext.errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- PO Number -->
                <b-col md="6" class="mb-2">
                  <validation-provider
                    name="PO Number"
                    :rules="{ required: true, min: 3, max: 55 }"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('PO Number') + ' ' + '*'">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="Name-feedback"
                        label="PO Number"
                        :placeholder="$t('PO Number')"
                        v-model="po.po_number"
                      ></b-form-input>
                      <b-form-invalid-feedback id="Name-feedback">{{
                        validationContext.errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- BarCode Symbology  -->
                <b-col md="6" class="mb-2">
                  <validation-provider
                    name="Barcode Symbology"
                    :rules="{ required: true }"
                  >
                    <b-form-group
                      slot-scope="{ valid, errors }"
                      :label="$t('BarcodeSymbology') + ' ' + '*'"
                    >
                      <v-select
                        :class="{ 'is-invalid': !!errors.length }"
                        :state="errors[0] ? false : valid ? true : null"
                        v-model="po.Type_barcode"
                        :reduce="(label) => label.value"
                        :placeholder="$t('Choose_Symbology')"
                        :options="[
                          { label: 'Code 128', value: 'CODE128' },
                          { label: 'Code 39', value: 'CODE39' },
                          { label: 'EAN8', value: 'EAN8' },
                          { label: 'EAN13', value: 'EAN13' },
                          { label: 'UPC', value: 'UPC' },
                        ]"
                      ></v-select>
                      <b-form-invalid-feedback>{{
                        errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Code Product"-->
                <b-col md="6" class="mb-2">
                  <validation-provider
                    name="Code Product"
                    :rules="{ required: true }"
                  >
                    <b-form-group
                      slot-scope="{ valid, errors }"
                      :label="$t('CodeProduct') + ' ' + '*'"
                    >
                      <div class="input-group">
                        <b-form-input
                          :class="{ 'is-invalid': !!errors.length }"
                          :state="errors[0] ? false : valid ? true : null"
                          aria-describedby="CodeProduct-feedback"
                          type="text"
                          v-model="po.code"
                        ></b-form-input>
                        <div class="input-group-append">
                          <span class="input-group-text">
                            <a @click="generateNumber()">
                              <i class="i-Bar-Code cursor-pointer"></i>
                            </a>
                          </span>
                        </div>
                        <b-form-invalid-feedback id="CodeProduct-feedback">{{
                          errors[0]
                        }}</b-form-invalid-feedback>
                      </div>
                      <span>{{
                        $t(
                          "Scan_your_barcode_and_select_the_correct_symbology_below"
                        )
                      }}</span>
                      <b-alert
                        show
                        variant="danger"
                        class="error mt-1"
                        v-if="code_exist != ''"
                        >{{ code_exist }}</b-alert
                      >
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Category -->
                <!-- <b-col md="6" class="mb-2">
                  <validation-provider name="category" :rules="{ required: true}">
                    <b-form-group
                      slot-scope="{ valid, errors }"
                      :label="$t('Categorie') + ' ' + '*'"
                    >
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        :reduce="label => label.value"
                        :placeholder="$t('Choose_Category')"
                        v-model="po.category_id"
                        :options="categories.map(categories => ({label: categories.name, value: categories.id}))"
                      />
                      <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col> -->

                <!-- Delivery Date -->
                <b-col md="6" class="mb-2">
                  <validation-provider
                    name="Delivery Date"
                    :rules="{ required: true }"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('Delivery Date') + ' ' + '*'">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="Name-feedback"
                        type="date"
                        label="Delivery Date"
                        :placeholder="$t('Delivery Date')"
                        v-model="po.delivery_date"
                      ></b-form-input>
                      <b-form-invalid-feedback id="Name-feedback">{{
                        validationContext.errors[0]
                      }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <b-col md="12" class="mb-2">
                  <b-form-group :label="$t('Description')">
                    <textarea
                      rows="4"
                      class="form-control"
                      :placeholder="$t('Afewwords')"
                      v-model="po.note"
                    ></textarea>
                  </b-form-group>
                </b-col>
              </b-row>
            </b-card>

            <b-card class="mt-3">
              <b-row>
                <!-- Type  -->
                <!-- <b-col md="6" class="mb-2">
                  <validation-provider name="Type" :rules="{ required: true}">
                    <b-form-group slot-scope="{ valid, errors }" :label="$t('type') + ' ' + '*'">
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="po.type"
                        :reduce="label => label.value"
                        :placeholder="$t('type')"
                        :options="
                            [
                            {label: 'Standard Product', value: 'is_single'},
                            {label: 'Variable Product', value: 'is_variant'},
                            {label: 'Service Product', value: 'is_service'}
                            ]"
                      ></v-select>
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="po.type"
                        :reduce="label => label.value"
                        :placeholder="$t('type')"
                        :options="
                            [
                            {label: 'Standard Product', value: 'is_single'},
                            {label: 'Variable Product', value: 'is_variant'}
                            ]"
                      ></v-select>
                      <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col> -->

                <!-- Type  -->
                <!-- <b-col md="6" class="mb-2">
                    <validation-provider name="ListingType" :rules="{ required: true}">
                      <b-form-group slot-scope="{ valid, errors }" :label="$t('listingType') + ' ' + '*'">
                        <v-select
                          :class="{'is-invalid': !!errors.length}"
                          :state="errors[0] ? false : (valid ? true : null)"
                          v-model="po.listingtype"
                          :reduce="label => label.value"
                          :placeholder="$t('listingType')"
                          :options="
                              [
                              {label: 'Reel', value: 'is_reel'},
                              {label: 'Roll', value: 'is_roll'},
                              // {label: 'Carton', value: 'is_carton'}
                              ]"
                        ></v-select>
                        <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col> -->

                <!-- Product Cost -->
                <!-- <b-col md="6" class="mb-2" v-if="po.type == 'is_single'">
                  <validation-provider
                    name="Product Cost"
                    :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('ProductCost') + ' ' + '*'">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="ProductCost-feedback"
                        label="Cost"
                        :placeholder="$t('Enter_Product_Cost')"
                        v-model="po.cost"
                      ></b-form-input>
                      <b-form-invalid-feedback
                        id="ProductCost-feedback"
                      >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col> -->

                <!-- Product Price -->
                <!-- <b-col
                  md="6"
                  class="mb-2"
                  v-if="po.type == 'is_single' || po.type == 'is_service'"
                >
                  <validation-provider
                    name="Product Price"
                    :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('ProductPrice') + ' ' + '*'">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="ProductPrice-feedback"
                        label="Price"
                        :placeholder="$t('Enter_Product_Price')"
                        v-model="po.price"
                      ></b-form-input>

                      <b-form-invalid-feedback
                        id="ProductPrice-feedback"
                      >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col> -->

                <!-- Unit Product -->
                <!-- <b-col md="6" class="mb-2" v-if="po.type != 'is_service'">
                  <validation-provider name="Unit Product" :rules="{ required: true}">
                    <b-form-group
                      slot-scope="{ valid, errors }"
                      :label="$t('UnitProduct') + ' ' + '*'"
                    >
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="po.unit_id"
                        class="required"
                        required
                        @input="Selected_Unit"
                        :placeholder="$t('Choose_Unit_Product')"
                        :reduce="label => label.value"
                        :options="units.map(units => ({label: units.name, value: units.id}))"
                      />
                      <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col> -->

                <!-- Unit Sale -->
                <!-- <b-col md="6" class="mb-2" v-if="po.type != 'is_service'">
                  <validation-provider name="Unit Sale" :rules="{ required: true}">
                    <b-form-group
                      slot-scope="{ valid, errors }"
                      :label="$t('UnitSale') + ' ' + '*'"
                    >
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="po.unit_sale_id"
                        :placeholder="$t('Choose_Unit_Sale')"
                        :reduce="label => label.value"
                        :options="units_sub.map(units_sub => ({label: units_sub.name, value: units_sub.id}))"
                      />
                      <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col> -->

                <!-- Unit Purchase -->
                <!-- <b-col md="6" class="mb-2" v-if="po.type != 'is_service'">
                  <validation-provider name="Unit Purchase" :rules="{ required: true}">
                    <b-form-group
                      slot-scope="{ valid, errors }"
                      :label="$t('UnitPurchase') + ' ' + '*'"
                    >
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="po.unit_purchase_id"
                        :placeholder="$t('Choose_Unit_Purchase')"
                        :reduce="label => label.value"
                        :options="units_sub.map(units_sub => ({label: units_sub.name, value: units_sub.id}))"
                      />
                      <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col> -->

                <!-- Stock Alert -->
                <!-- <b-col md="6" class="mb-2" v-if="po.type != 'is_service'">
                  <validation-provider
                    name="Stock Alert"
                    :rules="{ regex: /^\d*\.?\d*$/}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('StockAlert')">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="StockAlert-feedback"
                        label="Stock alert"
                        :placeholder="$t('Enter_Stock_alert')"
                        v-model="po.stock_alert"
                      ></b-form-input>
                      <b-form-invalid-feedback
                        id="StockAlert-feedback"
                      >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col> -->

                <div class="col-md-9 mb-3 mt-3">
                  <div class="d-flex">
                    <legend
                      tabindex="-1"
                      class="bv-no-focus-ring col-form-label pt-0"
                      id="__BVID__141__BV_label_"
                    >
                      Order Details
                    </legend>
                  </div>
                  <div class="d-flex">
                    <input
                      style="height: 40px"
                      placeholder="Enter the Size"
                      type="text"
                      name="variant"
                      v-model="size"
                      class="form-control"
                    />
                    <a
                      style="color: #ffff; margin-left: 10px"
                      @click="add_variant(size)"
                      class="ms-3 btn btn-md btn-primary"
                      >{{ $t("Add") }}</a
                    >
                  </div>
                </div>

                <div class="col-md-12 mb-2">
                  <div class="table">
                    <table class="table table-hover table-sm">
                      <thead class="bg-gray-300">
                        <tr>
                          <th scope="col">{{ $t("Carton Size") }}</th>
                          <th scope="col">{{ $t("Material") }}</th>
                          <th scope="col">{{ $t("Ply") }}</th>
                          <th scope="col">{{ $t("Quantity") }}</th>
                          <th scope="col">{{ $t("Paper Type") }}</th>
                          <th scope="col">{{ $t("Price") }}</th>
                          <th scope="col"></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="variants.length <= 0">
                          <td colspan="7">{{ $t("NodataAvailable") }}</td>
                        </tr>
                        <tr v-for="variant in variants">
                          <td>
                            <input
                              required
                              class="form-control"
                              v-model="variant.text"
                            />
                          </td>
                          <td>
                            <!-- <input
                              required
                              class="form-control"
                              v-model="variant.material"
                            /> -->
                            <v-select
                              v-model="variant.material"
                              :options="[
                                { label: 'BB+F 88/92', value: 'BB+F 88/92' },
                                {
                                  label: 'KBL160+F110/115',
                                  value: 'KBL160+F110/115',
                                },
                                {
                                  label: 'HP125+F110/115',
                                  value: 'HP125+F110/115',
                                },
                              ]"
                            ></v-select>
                          </td>
                          <td>
                            <!-- <input
                              required
                              class="form-control"
                              v-model="variant.ply"
                            /> -->
                            <v-select
                              v-model="variant.ply"
                              :options="[
                                { label: '1', value: '1' },
                                { label: '3', value: '3' },
                                { label: '5', value: '5' },
                                { label: '7', value: '7' },
                                { label: '9', value: '9' },
                              ]"
                            ></v-select>
                          </td>
                          <td>
                            <input
                              required
                              class="form-control"
                              v-model="variant.quantity"
                            />
                          </td>
                          <td>
                            <!-- <input
                              required
                              class="form-control"
                              v-model="variant.paperType"
                            /> -->
                            <v-select
                              v-model="variant.paperType"
                              :options="[
                                { label: 'Plain', value: 'Plain' },
                                { label: 'Die Cut', value: 'Die Cut' },
                                { label: 'Printed', value: 'Printed' },
                              ]"
                            ></v-select>
                          </td>
                          <td>
                            <input
                              required
                              class="form-control"
                              v-model="variant.price"
                            />
                            <!-- <v-select
                                v-model="variant.paperShade"
                                :options="
                                    [
                                    {label: 'Natural', value: 'Natural'},
                                    {label: 'Refish Craft', value: 'Refish Craft'},
                                    {label: 'Yellow', value: 'Yellow'},
                                    {label: 'White', value: 'White'}
                                    ]"
                                ></v-select> -->
                          </td>
                          <!-- <td>
                            // <input required class="form-control" v-model="variant.top">
                            <v-select
                              v-model="variant.top"
                              :options="[
                                { label: 'Yes', value: 'Yes' },
                                { label: 'No', value: 'No' },
                              ]"
                            ></v-select>
                          </td>
                          <td>
                            // <input required class="form-control" v-model="variant.flute">
                            <v-select
                              v-model="variant.flute"
                              :options="[
                                { label: 'Yes', value: 'Yes' },
                                { label: 'No', value: 'No' },
                              ]"
                            ></v-select>
                          </td>
                          <td>
                            // <input required class="form-control" v-model="variant.back">
                            <v-select
                              v-model="variant.back"
                              :options="[
                                { label: 'Yes', value: 'Yes' },
                                { label: 'No', value: 'No' },
                              ]"
                            ></v-select>
                          </td>
                          <td>
                            <input
                              required
                              class="form-control"
                              v-model="variant.cost"
                            />
                          </td>
                          <td>
                            <input
                              required
                              class="form-control"
                              v-model="variant.price"
                            />
                          </td> -->
                          <td>
                            <a
                              style="color: #ffff"
                              @click="delete_variant(variant.var_id)"
                              class="btn btn-sm btn-danger"
                              title="Delete"
                            >
                              <i class="i-Close-Window"></i>
                            </a>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </b-row>
            </b-card>

            <b-card class="mt-3">
              <!-- <b-row> -->
              <!-- Product_Has_Imei_Serial_number -->
              <!-- <b-col md="12 mb-2">
                  <ValidationProvider rules vid="product" v-slot="x">
                    <div class="form-check">
                      <label class="checkbox checkbox-outline-primary">
                        <input type="checkbox" v-model="po.is_imei">
                        <h5>{{$t('Product_Has_Imei_Serial_number')}}</h5>
                        <span class="checkmark"></span>
                      </label>
                    </div>
                  </ValidationProvider>
                </b-col> -->

              <!-- This_Product_Not_For_Selling -->
              <!-- <b-col md="12 mb-2">
                  <ValidationProvider rules vid="product" v-slot="x">
                    <div class="form-check">
                      <label class="checkbox checkbox-outline-primary">
                        <input type="checkbox" v-model="po.not_selling">
                        <h5>{{$t('This_Product_Not_For_Selling')}}</h5>
                        <span class="checkmark"></span>
                      </label>
                    </div>
                  </ValidationProvider>
                </b-col>
              </b-row>-->
            </b-card>
          </b-col>

          <!-- upload-multiple-image -->
          <!-- <b-col md="2">
            <b-card>
              <div class="card-header">
                <h5>{{$t('MultipleImage')}}</h5>
              </div>
              <div class="card-body">
                <b-row class="form-group">
                  <b-col md="12 mb-5">
                    <div
                      id="my-strictly-unique-vue-upload-multiple-image"
                      class="d-flex justify-content-center"
                    >
                      <vue-upload-multiple-image
                      @upload-success="uploadImageSuccess"
                      @before-remove="beforeRemove"
                      dragText="Drag & Drop Multiple images For product"
                      dropText="Drag & Drop image"
                      browseText="(or) Select"
                      accept=image/gif,image/jpeg,image/png,image/bmp,image/jpg
                      primaryText='success'
                      markIsPrimaryText='success'
                      popupText='have been successfully uploaded'
                      :data-images="images"
                      idUpload="myIdUpload"
                      :showEdit="false"
                      />
                    </div>
                  </b-col>
                </b-row>
              </div>
            </b-card>
          </b-col> -->
          <b-col md="12" class="mt-3">
            <b-button
              variant="primary"
              type="submit"
              :disabled="SubmitProcessing"
              ><i class="i-Yes me-2 font-weight-bold"></i>
              {{ $t("submit") }}</b-button
            >
            <div v-once class="typo__p" v-if="SubmitProcessing">
              <div class="spinner sm spinner-primary mt-3"></div>
            </div>
          </b-col>
        </b-row>
      </b-form>
    </validation-observer>
  </div>
</template>


<script>
import VueUploadMultipleImage from "vue-upload-multiple-image";
import VueTagsInput from "@johmun/vue-tags-input";
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Create Product",
  },
  data() {
    return {
      size: "",
      len: 8,
      change: false,
      isLoading: true,
      SubmitProcessing: false,
      data: new FormData(),
      clients: [],
      roles: {},
      variants: [],
      po: {
        client_id: "",
        order_date: "",
        export_date: "",
        po_number: "",
        Type_barcode: "CODE128",
        code: "",
        delivery_date: "",
        note: "",
      },
      code_exist: "",
    };
  },

  components: {
    VueUploadMultipleImage,
    VueTagsInput,
  },

  methods: {
    //------ Generate code
    generateNumber() {
      this.code_exist = "";
      this.po.code = Math.floor(
        Math.pow(10, 7) +
          Math.random() * (Math.pow(10, 8) - Math.pow(10, 7) - 1)
      );
    },

    //------------- Submit Validation Create Product
    Submit_Product() {
      this.$refs.Create_Product.validate().then((success) => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          if (this.po.type == "is_variant" && this.variants.length <= 0) {
            this.makeToast(
              "danger",
              "The product array is required.",
              this.$t("Failed")
            );
          } else {
            this.Create_Product();
          }
        }
      });
    },

    add_variant(tag) {
      if (
        this.variants.length > 0 &&
        this.variants.some((variant) => variant.text === tag)
      ) {
        this.makeToast(
          "warning",
          this.$t("VariantDuplicate"),
          this.$t("Warning")
        );
      } else {
        if (this.tag != "") {
          var variant_tag = {
            var_id: this.variants.length + 1, // generate unique ID
            text: tag,
          };
          this.variants.push(variant_tag);
          this.text = "";
        } else {
          this.makeToast(
            "warning",
            "Please enter the size",
            this.$t("Warning")
          );
        }
      }
    },

    //-----------------------------------Delete variant------------------------------\\
    delete_variant(var_id) {
      for (var i = 0; i < this.variants.length; i++) {
        if (var_id === this.variants[i].var_id) {
          this.variants.splice(i, 1);
        }
      }
    },

    //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true,
      });
    },

    //------ Validation State
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------ Event upload Image Success
    uploadImageSuccess(formData, index, fileList, imageArray) {
      this.images = fileList;
    },

    //------ Event before Remove Image
    beforeRemove(index, done, fileList) {
      var remove = confirm("remove image");
      if (remove == true) {
        this.images = fileList;
        done();
      } else {
      }
    },

    //-------------- Product Get Elements
    GetElements() {
      axios
        .get("purchase_order/create")
        .then((response) => {
          this.clients = response.data.clients;
          //   this.categories = response.data.categories;
          //   this.brands = response.data.brands;
          //   this.units = response.data.units;
          this.isLoading = false;
        })
        .catch((response) => {
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //---------------------- Get Sub Units with Unit id ------------------------------\\
    Get_Units_SubBase(value) {
      axios
        .get("get_sub_units_by_base?id=" + value)
        .then(({ data }) => (this.units_sub = data));
    },

    //---------------------- Event Select Unit Product ------------------------------\\
    Selected_Unit(value) {
      this.units_sub = [];
      this.po.unit_sale_id = "";
      this.po.unit_purchase_id = "";
      this.Get_Units_SubBase(value);
    },

    //------------------------------ Create new Product ------------------------------\\
    Create_Product() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      var self = this;
      self.SubmitProcessing = true;

      //   // append objet product
      if (self.po) {
        Object.entries(self.po).forEach(([key, value]) => {
          self.data.append(key, value);
        });
      } else {
        console.error("self.PO is undefined or null");
      }
        // append array variants
        if (self.variants.length) {
          self.data.append("variants", JSON.stringify(self.variants));
        }

      //   //append array images
      //   if (self.images.length > 0) {
      //     for (var k = 0; k < self.images.length; k++) {
      //       Object.entries(self.images[k]).forEach(([key, value]) => {
      //         self.data.append("images[" + k + "][" + key + "]", value);
      //       });
      //     }
      //   }

      // Send Data with axios
      axios
        .post("purchase_order", self.data)
        .then((response) => {
          // Complete the animation of theprogress bar.
          NProgress.done();
          self.SubmitProcessing = false;
          this.$router.push({ name: "index_purchase_order" });
          this.makeToast(
            "success",
            this.$t("Successfully_Created"),
            this.$t("Success")
          );
        })
        .catch((error) => {
          // Complete the animation of theprogress bar.
          NProgress.done();
          self.SubmitProcessing = false;
          if (error.errors.code && error.errors.code.length > 0) {
            self.code_exist = error.errors.code[0];
            this.makeToast("danger", error.errors.code[0], this.$t("Failed"));
          } else if (
            error.errors.variants &&
            error.errors.variants.length > 0
          ) {
            this.makeToast(
              "danger",
              error.errors.variants[0],
              this.$t("Failed")
            );
          } else {
            this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          }
        });
    },
  }, //end Methods

  //-----------------------------Created function-------------------

  created: function () {
    this.GetElements();
  },
};
</script>
