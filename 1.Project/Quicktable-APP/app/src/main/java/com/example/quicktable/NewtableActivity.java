package com.example.quicktable;

import android.content.Intent;
import android.graphics.Color;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import com.android.volley.Request;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.Volley;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
import java.util.HashMap;

public class NewtableActivity extends AppCompatActivity {

    private HashMap<String, Button> tableButtons = new HashMap<>();
    private static final String STATUS_URL = "http://192.168.201.3/QuicktableAPI/fetch_tablestatus.php";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_newtable);

        initializeButtons();
        fetchTableStatus();
    }

    @Override
    protected void onResume() {
        super.onResume();
        // Fetch table status whenever the activity resumes
        fetchTableStatus();
    }

    private void initializeButtons() {
        tableButtons.put("Table-1", findViewById(R.id.button1));
        tableButtons.put("Table-2", findViewById(R.id.button2));
        tableButtons.put("Table-3", findViewById(R.id.button3));
        tableButtons.put("Table-4", findViewById(R.id.button4));
        tableButtons.put("Table-5", findViewById(R.id.button5));
        tableButtons.put("Table-6", findViewById(R.id.button6));
        tableButtons.put("Table-7", findViewById(R.id.button7));
        tableButtons.put("Table-8", findViewById(R.id.button8));
        tableButtons.put("Table-9", findViewById(R.id.button9));
        tableButtons.put("Table-10", findViewById(R.id.button10));

        // Set button text to the full table name (e.g., "Table-1", "Table-2", etc.)
        for (String key : tableButtons.keySet()) {
            Button btn = tableButtons.get(key);
            if (btn != null) {
                btn.setText(key); // Set the full table name (e.g., "Table-1")
                btn.setOnClickListener(this::openTableActivity);
            }
        }
    }

    private void fetchTableStatus() {
        JsonArrayRequest request = new JsonArrayRequest(Request.Method.GET, STATUS_URL, null,
                response -> {
                    try {
                        // Reset all buttons to default state (green, enabled)
                        for (String key : tableButtons.keySet()) {
                            Button btn = tableButtons.get(key);
                            if (btn != null) {
                                btn.setBackgroundColor(Color.GREEN);
                                btn.setTextColor(Color.BLACK);
                                btn.setEnabled(true);
                                btn.setOnClickListener(this::openTableActivity);
                            }
                        }

                        // Update button states based on fetched status
                        for (int i = 0; i < response.length(); i++) {
                            JSONObject table = response.getJSONObject(i);
                            String tableNo = table.getString("table_no");
                            String status = table.getString("status");

                            Button btn = tableButtons.get(tableNo);
                            if (btn != null) {
                                if (status.equals("occupied")) {
                                    btn.setBackgroundColor(Color.RED);
                                    btn.setTextColor(Color.WHITE);
                                    btn.setEnabled(true); // Keep button clickable
                                    btn.setOnClickListener(v ->
                                            Toast.makeText(this, "Table is occupied", Toast.LENGTH_SHORT).show()
                                    );
                                }
                            }
                        }
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }
                },
                error -> {
                    error.printStackTrace();
                    Toast.makeText(this, "Failed to fetch table status: " + error.getMessage(), Toast.LENGTH_LONG).show();
                });

        Volley.newRequestQueue(this).add(request);
    }

    public void openTableActivity(View view) {
        for (String key : tableButtons.keySet()) {
            if (tableButtons.get(key).getId() == view.getId()) {
                Intent intent = new Intent(NewtableActivity.this, BtnActivity.class);
                intent.putExtra("TABLE_NAME", key);
                startActivity(intent);
                break;
            }
        }
    }
}