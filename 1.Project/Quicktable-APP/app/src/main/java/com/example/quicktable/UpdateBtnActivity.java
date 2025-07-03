package com.example.quicktable;

import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.AutoCompleteTextView;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.Request;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

public class UpdateBtnActivity extends AppCompatActivity {

    private static final String SUGGESTION_URL = "http://192.168.201.3/QuicktableAPI/fetch_suggestion.php";
    private static final String SAVE_URL = "http://192.168.201.3/QuicktableAPI/post_order.php";
    private static final String FETCH_TOKEN_URL = "http://192.168.201.3/QuicktableAPI/fetch_updatetoken.php";
    private static final String UPDATE_STATUS_URL = "http://192.168.201.3/QuicktableAPI/update_orderstatus.php"; // New URL for updating status
    private List<String> itemNames = new ArrayList<>();
    private String tokenNumber;
    private String tableName;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_update_btn);

        // Get the table name from the intent
        tableName = getIntent().getStringExtra("TABLE_NAME");
        String tableNumber = tableName.replace("Table-", "");

        // Set the table name in the TextView
        TextView textView = findViewById(R.id.tableTextView);
        textView.setText("Table name: " + tableNumber);

        // Fetch the token number for the table
        TextView tokenTextView = findViewById(R.id.tokenTextView);
        fetchTokenNumber(tokenTextView, tableName);

        // Initialize buttons
        Button addItemBtn = findViewById(R.id.addItemBtn);
        Button saveButton = findViewById(R.id.saveButton);
        Button finishedButton = findViewById(R.id.finishedButton);

        // Set up the initial item container
        View initialContainer = findViewById(R.id.mainContainer);
        setupItemContainer(initialContainer);

        // Fetch item suggestions for the AutoCompleteTextView
        fetchItemSuggestions();

        // Add item button click listener
        addItemBtn.setOnClickListener(v -> {
            if (areAllContainersFilled()) {
                addNewItemContainer();
            } else {
                Toast.makeText(this, "Please fill in all fields before adding a new item", Toast.LENGTH_SHORT).show();
            }
        });

        // Save button click listener
        saveButton.setOnClickListener(v -> saveOrder());

        // Finished button click listener
        finishedButton.setOnClickListener(v -> updateStatusToFinished());
    }

    // Fetch the token number for the table
    private void fetchTokenNumber(TextView tokenTextView, String tableName) {
        String url = FETCH_TOKEN_URL + "?table_no=" + tableName;
        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.GET,
                url,
                null,
                response -> {
                    try {
                        tokenNumber = response.getString("next_token");
                        tokenTextView.setText("Token Number: " + tokenNumber);
                    } catch (JSONException e) {
                        Log.e("FetchToken", "JSON Error: " + e.getMessage());
                        Toast.makeText(this, "Error fetching token number", Toast.LENGTH_SHORT).show();
                    }
                },
                error -> {
                    Log.e("FetchToken", "Volley Error: " + error.toString());
                    Toast.makeText(this, "Failed to fetch token number", Toast.LENGTH_SHORT).show();
                }
        );
        Volley.newRequestQueue(this).add(request);
    }

    // Set up the item container (AutoCompleteTextView, EditText, and buttons)
    private void setupItemContainer(View itemContainer) {
        AutoCompleteTextView autoComplete = itemContainer.findViewById(R.id.autoCompleteTextView);
        EditText etNumber = itemContainer.findViewById(R.id.etNumber);
        ImageButton btnDecrease = itemContainer.findViewById(R.id.btnDecrease);
        ImageButton btnIncrease = itemContainer.findViewById(R.id.btnIncrease);

        // Set default quantity to 0
        etNumber.setText("0");

        // Set up the adapter for the AutoCompleteTextView
        ArrayAdapter<String> adapter = new ArrayAdapter<>(this, android.R.layout.simple_dropdown_item_1line, itemNames);
        autoComplete.setAdapter(adapter);
        autoComplete.setThreshold(1); // Show suggestions after 1 character

        // Handle item selection in AutoCompleteTextView
        autoComplete.setOnItemClickListener((parent, view, position, id) -> {
            String selectedItem = (String) parent.getItemAtPosition(position);
            autoComplete.setText(selectedItem); // Set the selected item
        });

        // Increase quantity button click listener
        btnIncrease.setOnClickListener(v -> {
            int current = Integer.parseInt(etNumber.getText().toString());
            etNumber.setText(String.valueOf(current + 1));
        });

        // Decrease quantity button click listener
        btnDecrease.setOnClickListener(v -> {
            int current = Integer.parseInt(etNumber.getText().toString());
            if (current > 0) {
                etNumber.setText(String.valueOf(current - 1));
            } else {
                Toast.makeText(this, "Value cannot be less than 0", Toast.LENGTH_SHORT).show();
            }
        });
    }

    // Check if all containers are filled
    private boolean areAllContainersFilled() {
        LinearLayout containerLayout = findViewById(R.id.dynamicContainerLayout);
        for (int i = 0; i < containerLayout.getChildCount(); i++) {
            View child = containerLayout.getChildAt(i);
            AutoCompleteTextView actv = child.findViewById(R.id.autoCompleteTextView);
            EditText et = child.findViewById(R.id.etNumber);

            if (actv.getText().toString().isEmpty() || et.getText().toString().equals("0")) {
                return false;
            }
        }
        return true;
    }

    // Add a new item container
    private void addNewItemContainer() {
        LinearLayout containerLayout = findViewById(R.id.dynamicContainerLayout);
        View newItem = getLayoutInflater().inflate(R.layout.item_container, containerLayout, false);

        // Set layout parameters for the new item container
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.WRAP_CONTENT
        );
        params.setMargins(10, 60, 0, 0); // Add top margin
        newItem.setLayoutParams(params);

        // Set up the new item container
        setupItemContainer(newItem);

        // Add the new item container to the layout
        containerLayout.addView(newItem);
    }

    // Fetch item suggestions for the AutoCompleteTextView
    private void fetchItemSuggestions() {
        JsonArrayRequest request = new JsonArrayRequest(
                Request.Method.GET,
                SUGGESTION_URL,
                null,
                response -> {
                    try {
                        itemNames.clear();
                        for (int i = 0; i < response.length(); i++) {
                            itemNames.add(response.getString(i));
                        }
                    } catch (JSONException e) {
                        Log.e("FetchItems", "JSON Error: " + e.getMessage());
                    }
                },
                error -> Log.e("FetchItems", "Volley Error: " + error.toString())
        );
        Volley.newRequestQueue(this).add(request);
    }

    // Save the order to the server
    private void saveOrder() {
        LinearLayout containerLayout = findViewById(R.id.dynamicContainerLayout);
        JSONArray ordersArray = new JSONArray();

        // Loop through all item containers
        for (int i = 0; i < containerLayout.getChildCount(); i++) {
            View child = containerLayout.getChildAt(i);
            AutoCompleteTextView actv = child.findViewById(R.id.autoCompleteTextView);
            EditText et = child.findViewById(R.id.etNumber);

            // Skip empty or invalid items
            if (actv.getText().toString().isEmpty() || et.getText().toString().equals("0")) {
                continue;
            }

            // Create a JSON object for the order
            JSONObject orderObject = new JSONObject();
            try {
                orderObject.put("item_name", actv.getText().toString());
                orderObject.put("quantity", et.getText().toString());
                orderObject.put("token_number", tokenNumber);
                ordersArray.put(orderObject);
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }

        // Send the order to the server if there are valid items
        if (ordersArray.length() > 0) {
            sendToServer(ordersArray);
        } else {
            Toast.makeText(this, "No valid items to save", Toast.LENGTH_SHORT).show();
        }
    }

    // Send the order data to the server
    private void sendToServer(JSONArray ordersArray) {
        JSONObject requestBody = new JSONObject();
        try {
            requestBody.put("orders", ordersArray);
            requestBody.put("table_no", tableName);
        } catch (JSONException e) {
            e.printStackTrace();
        }

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.POST,
                SAVE_URL,
                requestBody,
                response -> Toast.makeText(this, "Orders saved successfully!", Toast.LENGTH_SHORT).show(),
                error -> Log.e("ServerError", "Error: " + error.toString())
        );
        Volley.newRequestQueue(this).add(request);
    }

    // Update the status to "finished" for the current table and token
    private void updateStatusToFinished() {
        JSONObject requestBody = new JSONObject();
        try {
            requestBody.put("table_no", tableName);
            requestBody.put("token_number", tokenNumber);
        } catch (JSONException e) {
            e.printStackTrace();
        }

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.POST,
                UPDATE_STATUS_URL,
                requestBody,
                response -> {
                    Toast.makeText(this, "Status updated to finished!", Toast.LENGTH_SHORT).show();
                    finish(); // Navigates back to the previous page
                },
                error -> Log.e("UpdateStatus", "Error: " + error.toString())
        );

        Volley.newRequestQueue(this).add(request);
    }
}
