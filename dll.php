$handle = FFI::cdef(
    "int add_numbers(int a, int b);",
    "path/to/mymath.dll"
);

$sum = $handle->add_numbers(10, 7);
echo "Sum: " . $sum; // Output: Sum: 17


//Let's say you have a DLL named mymath.dll with a function add_numbers(int a, int b) that adds two integers.

//This code would load the mymath.dll library and call the add_numbers() function to add 10 and 7, resulting in 17.