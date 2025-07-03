package com.example.quicktable;

import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.Volley;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

public class CookActivity extends AppCompatActivity {

    private LinearLayout messageContainer;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_cook);

        // Correct ID to match XML
        messageContainer = findViewById(R.id.messageContainer);

        // Set up the Back button
        Button buttonBack = findViewById(R.id.buttonBack);
        buttonBack.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // Navigate back to the previous activity
                onBackPressed();
            }
        });

        fetchCookDetails();
    }

    private void fetchCookDetails() {
        String url = "http://192.168.201.3/QuicktableAPI/fetch_cookdetails.php";

        RequestQueue queue = Volley.newRequestQueue(this);
        JsonArrayRequest jsonArrayRequest = new JsonArrayRequest(Request.Method.GET, url, null,
                new Response.Listener<JSONArray>() {
                    @Override
                    public void onResponse(JSONArray response) {
                        try {
                            messageContainer.removeAllViews();

                            for (int i = 0; i < response.length(); i++) {
                                JSONObject tableOrder = response.getJSONObject(i);
                                addTableOrder(tableOrder);
                            }
                        } catch (JSONException e) {
                            e.printStackTrace();
                            Toast.makeText(CookActivity.this, "Error parsing data", Toast.LENGTH_SHORT).show();
                        }
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        Toast.makeText(CookActivity.this, "Error fetching data", Toast.LENGTH_SHORT).show();
                    }
                });

        queue.add(jsonArrayRequest);
    }

    private void addTableOrder(JSONObject tableOrder) throws JSONException {
        // Inflate table container
        View tableView = getLayoutInflater().inflate(R.layout.table_container, null);
        TextView tvTableNo = tableView.findViewById(R.id.textViewTableNo);
        TextView tvTokenNo = tableView.findViewById(R.id.textViewTokenNo);
        LinearLayout itemsContainer = tableView.findViewById(R.id.itemsContainer);
        Button btnDone = tableView.findViewById(R.id.buttonDone);

        String tableNo = tableOrder.getString("table_no");
        String tokenNo = tableOrder.getString("token_number");

        tvTableNo.setText("Table No: " + tableNo);
        tvTokenNo.setText("Token No: " + tokenNo);

        // Add all items
        JSONArray items = tableOrder.getJSONArray("items");
        for (int i = 0; i < items.length(); i++) {
            JSONObject item = items.getJSONObject(i);
            View itemView = getLayoutInflater().inflate(R.layout.item_row, null);

            TextView tvItemName = itemView.findViewById(R.id.textViewItemName);
            TextView tvQty = itemView.findViewById(R.id.textViewQty);

            tvItemName.setText(item.getString("item_name"));
            tvQty.setText(String.valueOf(item.getInt("quantity")));

            itemsContainer.addView(itemView);
        }

        // Handle Done button
        btnDone.setOnClickListener(v -> {
            // Mark all items for this table as done
            markOrderAsDone(tableNo, tokenNo);
            messageContainer.removeView(tableView);
        });

        // Set margins programmatically
        LinearLayout.LayoutParams layoutParams = new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.WRAP_CONTENT
        );
        layoutParams.setMargins(0, 0, 0, 16); // Set bottom margin to 16dp
        tableView.setLayoutParams(layoutParams);

        // Add the table container to the messageContainer
        messageContainer.addView(tableView);
    }

    private void markOrderAsDone(String tableNo, String tokenNo) {
        // Implement your logic to update status in the database
        Toast.makeText(this, "Table " + tableNo + " marked as done", Toast.LENGTH_SHORT).show();
    }
}