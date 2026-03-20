import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import "./Testimonials.scss";

export type Testimonial = {
  quote: string;
  name: string;
};

type TestimonialsProps = {
  testimonials: Testimonial[];
};

export default function Testimonials({ testimonials }: TestimonialsProps) {
  return (
    <div id="testimonials">
      {testimonials.map((testimonial) => (
        <div key={testimonial.name} className="testimonial">
          <FontAwesomeIcon icon="quote-left" aria-hidden />
          <div>
            <p>{testimonial.quote}</p>
            <span>{testimonial.name}</span>
          </div>
          <FontAwesomeIcon icon="quote-right" aria-hidden />
        </div>
      ))}
    </div>
  );
}
